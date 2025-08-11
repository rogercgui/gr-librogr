<?php
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'csl_functions.php';

use Seboettg\CiteProc\StyleSheet;
use Seboettg\CiteProc\CiteProc;

include 'templates/header.php';

// --- ROTEAMENTO PRINCIPAL ---
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;
$selected_style = $_GET['style'] ?? DEFAULT_CSL_STYLE;
$available_styles = getAvailableStyles();

switch ($action) {
    case 'new':
    case 'edit':
        $reference = getReferenceById($id);
        include 'templates/form.php';
        break;
    case 'view':
        $reference = getReferenceById($id);
        if ($reference) {
            include 'templates/view.php';
        } else {
            echo "<h2>Erro</h2><p>Referência não encontrada.</p>";
        }
        break;
    case 'search':
        $query = strtolower(trim($_GET['q'] ?? ''));
        $all_references = getReferences();
        $results = array_filter($all_references, function ($ref) use ($query) {
            $search_string = json_encode($ref, JSON_UNESCAPED_UNICODE);
            return strpos(strtolower($search_string), $query) !== false;
        });
        display_references(array_values($results), "Resultados da Busca", $page, $items_per_page, $selected_style, $available_styles);
        break;
    case 'list':
    default:
        $all_references = getReferences();
        display_references($all_references, "Últimas Referências", $page, $items_per_page, $selected_style, $available_styles);
        break;
}

/**
 * Função final para exibir a lista de referências.
 */
function display_references($references, $title, $page, $items_per_page, $selected_style, $available_styles)
{
    $total_references = count($references);
    $total_pages = ceil($total_references / $items_per_page);
    $offset = ($page - 1) * $items_per_page;
    $paginated_references = array_slice($references, $offset, $items_per_page);
    $query_params = $_GET;
    $base_url = 'index.php?' . http_build_query(array_merge($query_params, ['page' => '']));

    $formatted_entries = [];
    $csl_error = null;
    $csl_styles_css = '';
    if ($selected_style && !empty($paginated_references)) {
        $csl_file_path = __DIR__ . '/refmodels/' . $selected_style;
        if (file_exists($csl_file_path)) {
            try {
                $style = StyleSheet::loadStyleSheet($csl_file_path);
                $locale = get_csl_locale($csl_file_path);
                $citeProc = new CiteProc($style, $locale);

                $all_csl_data = array_map('convertToCSLJSON', $paginated_references);

                $bibliography_html = $citeProc->render($all_csl_data, 'bibliography');
                $csl_styles_css = $citeProc->renderCssStyles();

                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                $dom->loadHTML('<?xml encoding="utf-8"?>' . $bibliography_html);
                $xpath = new DOMXPath($dom);
                $nodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " csl-entry ")]');
                foreach ($nodes as $node) {
                    $inner = '';
                    foreach ($node->childNodes as $child) {
                        $inner .= $dom->saveHTML($child);
                    }
                    $formatted_entries[] = $inner;
                }
                libxml_clear_errors();
            } catch (Exception $e) {
                $csl_error = "Erro ao renderizar CSL: " . $e->getMessage();
            }
        } else {
            $csl_error = "Arquivo de estilo não encontrado: " . htmlspecialchars($selected_style);
        }
    }
?>
    <style type="text/css">
        <?php echo $csl_styles_css; ?>
    </style>

    <?php if (!empty($available_styles)): ?>
        <div class="style-selector">
            <form action="index.php" method="get" id="styleForm">
                <?php foreach ($_GET as $key => $value) if (!in_array($key, ['style', 'page'])) echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">'; ?>
                <label for="style">Formato de Visualização:</label>
                <select name="style" id="style" onchange="this.form.submit();">
                    <option value="">Padrão do Sistema</option>
                    <?php foreach ($available_styles as $file => $name) : ?>
                        <option value="<?php echo $file; ?>" <?php echo ($selected_style === $file) ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <h2><?php echo $title; ?> <?php if (isset($_GET['q'])) echo "<a href='index.php' style='font-size:0.8em;'>(Limpar)</a>"; ?></h2>

    <div class="reference-list">
        <?php if ($csl_error): ?><p style="color:red; font-weight:bold;"><?php echo $csl_error; ?></p><?php endif; ?>
        <ul>
            <?php if (empty($paginated_references)) : ?> <li>Nenhuma referência encontrada.</li> <?php else : ?>
                <?php foreach ($paginated_references as $index => $ref) : ?>
                    <li>
                        <div class="reference-info">
                            <?php
                                                                                                        if (!empty($formatted_entries) && isset($formatted_entries[$index])) {
                                                                                                            echo $formatted_entries[$index];
                                                                                                        } else {
                                                                                                            $year = $ref->issued->{'date-parts'}[0][0] ?? 'S/A';
                                                                                                            $author_names = [];
                                                                                                            $authors_array = $ref->author ?? [];
                                                                                                            if (is_array($authors_array)) {
                                                                                                                foreach ($authors_array as $author) {
                                                                                                                    if (isset($author->literal)) {
                                                                                                                        $author_names[] = $author->literal;
                                                                                                                    } else {
                                                                                                                        $author_names[] = trim(($author->given ?? '') . ' ' . ($author->family ?? ''));
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                            $author_string = implode('; ', $author_names);
                                                                                                            echo "<strong>" . htmlspecialchars($ref->title ?? 'Sem Título') . "</strong> (" . htmlspecialchars($year) . ")<br>";
                                                                                                            echo "<em>" . htmlspecialchars($author_string) . "</em>";
                                                                                                        }
                            ?>
                        </div>
                        <div class="reference-actions">
                            <?php $url = $ref->url ?? $ref->URL ?? null;
                                                                                                        if ($url): ?>
                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info">URL</a>
                            <?php endif; ?>
                            <a href="index.php?action=view&id=<?php echo $ref->id; ?>" class="btn-primary btn">Ver</a>
                            <a href="index.php?action=edit&id=<?php echo $ref->id; ?>" class="btn-secondary btn">Editar</a>
                            <a href="Acoes.php?action=delete&id=<?php echo $ref->id; ?>" onclick="return confirm('Tem certeza?');" class="btn-danger btn">Apagar</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="pagination">
        <?php if ($page > 1) : ?> <a href="<?php echo str_replace('page=', 'page=' . ($page - 1), $base_url); ?>" class="btn btn-secondary">Anterior</a> <?php endif; ?>
        <?php if ($page < $total_pages) : ?> <a href="<?php echo str_replace('page=', 'page=' . ($page + 1), $base_url); ?>" class="btn btn-secondary">Próxima</a> <?php endif; ?>
    </div>
<?php
}

include 'templates/footer.php';
?>