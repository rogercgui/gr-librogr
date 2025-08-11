<?php
// Requer as funções de ajuda CSL e de exportação
require_once __DIR__ . '/../csl_functions.php';
require_once __DIR__ . '/../export_functions.php';

use Seboettg\CiteProc\StyleSheet;
use Seboettg\CiteProc\CiteProc;

// Pega os estilos CSL disponíveis para o seletor
$available_styles = getAvailableStyles();
// Pega o estilo selecionado da URL ou usa o padrão
$selected_style = $_GET['style'] ?? DEFAULT_CSL_STYLE;

// Gera a citação formatada com o estilo CSL selecionado
$formatted_csl_entry = '';
$csl_error = '';
if ($selected_style && file_exists(__DIR__ . '/../refmodels/' . $selected_style)) {
    try {
        $csl_file_path = __DIR__ . '/../refmodels/' . $selected_style;
        $style = StyleSheet::loadStyleSheet($csl_file_path);
        $locale = get_csl_locale($csl_file_path);
        $citeProc = new CiteProc($style, $locale);
        $csl_data = convertToCSLJSON($reference);
        $bibliography_html = $citeProc->render([$csl_data], 'bibliography');

        $temp_entry = explode('<div class="csl-entry">', $bibliography_html)[1] ?? '';
        $formatted_csl_entry = rtrim(trim($temp_entry), "</div>");
    } catch (Exception $e) {
        $csl_error = "Erro ao renderizar CSL: " . $e->getMessage();
    }
}

// Gera os formatos de exportação (BibTeX, RIS, etc.)
$reference_bibtex = generateBibTeX($reference);
$reference_ris = generateRIS($reference);
$reference_biblatex = generateBibTeX($reference);
?>

<div class="view-actions">
    <a href="index.php" class="btn btn-secondary">Início</a>
    <?php
    // Lógica robusta para encontrar a URL, seja a chave 'url' ou 'URL'
    $url = $reference->url ?? $reference->URL ?? null;
    if ($url):
    ?>
        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-info">Acessar URL</a>
    <?php endif; ?>
    <a href="index.php?action=edit&id=<?php echo $reference->id; ?>" class="btn btn-primary">Editar</a>
</div>



<div class="csl-preview">
    <h3>Pré-visualização da Citação</h3>
    <?php if (!empty($available_styles)): ?>
        <div class="style-selector" style="margin-bottom: 15px;">
            <form action="index.php" method="get" id="styleForm">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="id" value="<?php echo $reference->id; ?>">

                <label for="style">Visualizar com o estilo:</label>
                <select name="style" id="style" onchange="this.form.submit();">
                    <option value="">Padrão do Sistema</option>
                    <?php foreach ($available_styles as $file => $name) : ?>
                        <option value="<?php echo $file; ?>" <?php echo ($selected_style === $file) ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <hr>

    <div class="formatted-reference-view">
        <?php if ($csl_error): ?>
            <p style="color:red;"><?php echo $csl_error; ?></p>
        <?php else: ?>
            <?php echo $formatted_csl_entry; ?>
        <?php endif; ?>
    </div>

    <hr>

    <?php
    // Pega as anotações para este registro
    $annotations = getAnnotationByRefId($reference->id);
    ?>
    <?php if (!empty($annotations['notas']) || !empty($annotations['resumo'])): ?>
        <div class="annotations-view">
            <h3>Suas Anotações</h3>
            <?php if (!empty($annotations['notas'])): ?>
                <h4>Notas</h4>
                <p><?php echo nl2br(htmlspecialchars($annotations['notas'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($annotations['resumo'])): ?>
                <h4>Resumo</h4>
                <p><?php echo nl2br(htmlspecialchars($annotations['resumo'])); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <hr>
    
    <div class="formatted-output">
        <h3>Exportar Citação</h3>
        <label for="format-select">Selecione o formato:</label>
        <select id="format-select">
            <option value="bibtex" selected>BibTeX</option>
            <option value="biblatex">BibLaTeX</option>
            <option value="ris">RIS</option>
        </select>
        <button id="copy-button" class="btn btn-secondary" style="margin-left: 10px;">Copiar</button>

        <div id="output-bibtex" class="format-display">
            <pre><code><?php echo htmlspecialchars($reference_bibtex); ?></code></pre>
        </div>
        <div id="output-biblatex" class="format-display" style="display: none;">
            <pre><code><?php echo htmlspecialchars($reference_biblatex); ?></code></pre>
        </div>
        <div id="output-ris" class="format-display" style="display: none;">
            <pre><code><?php echo htmlspecialchars($reference_ris); ?></code></pre>
        </div>
    </div>

    <div class="reference-details">
        <h3>Detalhes do Registro</h3>
        <ul>
            <?php
            $ref_array = get_object_vars($reference);
            foreach ($ref_array as $key => $value):
                if (in_array($key, ['id', 'timestamp'])) continue;

                $display_value = '';

                // --- LÓGICA ATUALIZADA AQUI ---
                if ($key === 'url' && !empty($value)) {
                    // Se a chave for URL, cria um link
                    $display_value = '<a href="' . htmlspecialchars($value) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($value) . '</a>';
                } else if (is_object($value) || is_array($value)) {
                    // Lógica existente para formatar autores e datas
                    if ($key === 'author' && is_array($value)) {
                        $author_names = [];
                        foreach ($value as $author) {
                            if (isset($author->literal)) {
                                $author_names[] = $author->literal;
                            } else {
                                $author_names[] = trim(($author->given ?? '') . ' ' . ($author->family ?? ''));
                            }
                        }
                        $display_value = htmlspecialchars(implode('; ', $author_names));
                    } elseif ($key === 'issued' && isset($value->{'date-parts'}[0])) {
                        $display_value = htmlspecialchars(implode('-', $value->{'date-parts'}[0]));
                    } else {
                        $display_value = htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE));
                    }
                } else {
                    $display_value = htmlspecialchars($value);
                }
            ?>
                <li><strong><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $key))); ?>:</strong> <?php echo $display_value; // A variável já contém HTML seguro 
                                                                                                            ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <details class="csl-verification" style="margin-top: 30px;">
        <summary>Verificação do Formato CSL-JSON</summary>
        <pre style="background-color: #f0f0f0; border: 1px solid #ccc; padding: 10px; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars(json_encode($reference, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    </details>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formatSelect = document.getElementById('format-select');
            const copyButton = document.getElementById('copy-button');
            const displays = {
                bibtex: document.getElementById('output-bibtex'),
                biblatex: document.getElementById('output-biblatex'),
                ris: document.getElementById('output-ris')
            };
            formatSelect.addEventListener('change', function() {
                for (const format in displays) {
                    displays[format].style.display = (format === this.value) ? 'block' : 'none';
                }
            });
            copyButton.addEventListener('click', function() {
                const selectedFormat = formatSelect.value;
                const textToCopy = displays[selectedFormat].querySelector('code').innerText;
                navigator.clipboard.writeText(textToCopy).then(() => {
                    copyButton.innerText = 'Copiado!';
                    setTimeout(() => {
                        copyButton.innerText = 'Copiar';
                    }, 2000);
                }, (err) => {
                    alert('Erro ao copiar.');
                });
            });
        });
    </script>