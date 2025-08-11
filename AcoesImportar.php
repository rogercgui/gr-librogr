<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['reference_file'])) {
    $file = $_FILES['reference_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Erro no upload do arquivo.");
    }

    $file_content = file_get_contents($file['tmp_name']);

    // O parser agora gera CSL-JSON diretamente
    $new_references = parseBibTeX($file_content);

    if (empty($new_references)) {
        die("Nenhuma referência válida encontrada no arquivo ou formato não suportado.");
    }

    $existing_references = getReferences();
    $existing_citekeys = array_column($existing_references, 'citekey');

    $imported_count = 0;
    foreach ($new_references as $ref) {
        if (!in_array($ref['citekey'], $existing_citekeys)) {
            $existing_references[] = $ref;
            $imported_count++;
        }
    }

    saveReferences($existing_references);

    echo "Importação concluída! {$imported_count} novas referências foram adicionadas. <a href='index.php'>Voltar para a lista</a>";
} else {
    header('Location: import.php');
    exit();
}

/**
 * Analisa uma string BibTeX e converte para um array de CSL-JSON.
 * @param string $content O conteúdo do arquivo .bib
 * @return array
 */
// Em AcoesImportar.php, substitua a função parseBibTeX por esta:
function parseBibTeX($content)
{
    $references = [];
    preg_match_all('/@(\w+)\s*\{\s*([^,]+),([\s\S]*?)\s*\}\s*(?=@|$)/s', $content, $entries, PREG_SET_ORDER);

    foreach ($entries as $entry) {
        $bib_type = strtolower($entry[1]);
        $citekey = trim($entry[2]);
        $fields_str = $entry[3];

        $current_ref = ['id' => uniqid('ref_', true), 'timestamp' => time(), 'citekey' => $citekey];
        $type_map = ['article' => 'article-journal', 'book' => 'book', 'incollection' => 'chapter', 'online' => 'webpage', 'misc' => 'webpage', 'web' => 'webpage'];
        $current_ref['type'] = $type_map[$bib_type] ?? 'article';

        preg_match_all('/([a-zA-Z0-9]+)\s*=\s*(?:\{([\s\S]*?)\}|"([\s\S]*?)")\s*(?:,|$)/', $fields_str, $fields, PREG_SET_ORDER);
        $temp_fields = [];
        foreach ($fields as $field) {
            $key = strtolower(trim($field[1]));
            $value = trim($field[2] !== '' ? $field[2] : $field[3]);
            $value = preg_replace('/\s+/', ' ', $value);
            $temp_fields[$key] = $value;
        }

        // Mapeamento de campos BibTeX para CSL-JSON
        $field_map = ['journal' => 'container-title', 'number' => 'issue', 'pages' => 'page', 'address' => 'publisher-place'];

        foreach ($temp_fields as $key => $value) {
            if ($key === 'author') {
                $authors = [];
                $author_list = explode(' and ', $value);
                foreach ($author_list as $author_str) {
                    $author_str = trim($author_str);
                    $author_object = new stdClass();
                    if (substr($author_str, 0, 1) === '{' && substr($author_str, -1) === '}') {
                        $author_object->literal = trim($author_str, '{}');
                    } else if (strpos($author_str, ',') !== false) {
                        $parts = explode(',', $author_str, 2);
                        $author_object->family = trim($parts[0]);
                        $author_object->given = trim($parts[1]);
                    } else {
                        $parts = explode(' ', $author_str);
                        $author_object->family = array_pop($parts);
                        $author_object->given = implode(' ', $parts);
                    }
                    $authors[] = $author_object;
                }
                $current_ref['author'] = $authors;
            } else if ($key === 'year') {
                $date_object = new stdClass();
                $date_object->{'date-parts'} = [[$value]];
                $current_ref['issued'] = $date_object;
            } else if ($key === 'howpublished' && strpos($value, 'url{') !== false) {
                $current_ref['URL'] = trim(str_replace(['\url{', '}'], '', $value));
            } else if (isset($field_map[$key])) {
                $csl_key = $field_map[$key];
                $current_ref[$csl_key] = str_replace('--', '-', $value);
            } else {
                $current_ref[$key] = $value;
            }
        }
        $references[] = $current_ref;
    }
    return $references;
}
