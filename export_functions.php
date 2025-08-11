<?php

function format_csl_authors_to_string($authors, $separator = ' and ')
{
    if (empty($authors) || !is_array($authors)) return '';

    $author_strings = [];
    foreach ($authors as $author) {
        // CORREÇÃO: Usa -> para acessar propriedades do objeto
        if (isset($author->literal)) {
            $author_strings[] = '{' . $author->literal . '}';
        } else {
            $author_strings[] = ($author->family ?? '') . ', ' . ($author->given ?? '');
        }
    }
    return implode($separator, $author_strings);
}


function generateBibTeX($ref)
{
    $type = $ref->type ?? 'misc';
    if ($type === 'article-journal') $type = 'article';
    if ($type === 'webpage') $type = 'online';

    $citekey = $ref->citekey ?? $ref->id;
    $output = "@{$type}{{$citekey},\n";
    $mappings = [
        'container-title' => 'journal',
        'issue' => 'number',
        'page' => 'pages',
        'URL' => 'url'
    ];

    // O foreach funciona para propriedades de objetos
    foreach ($ref as $key => $value) {
        if (is_array($value) || is_object($value) || in_array($key, ['id', 'type', 'timestamp', 'citekey'])) continue;

        $bib_key = $mappings[$key] ?? $key;
        $output .= "  " . strtolower($bib_key) . " = {" . $value . "},\n";
    }

    // CORREÇÃO: Usa -> para acessar propriedades do objeto
    $output .= "  author = {" . format_csl_authors_to_string($ref->author ?? []) . "},\n";
    $output .= "  year = {" . ($ref->issued->{'date-parts'}[0][0] ?? '') . "},\n";

    return rtrim($output, ",\n") . "\n}\n";
}

function generateRIS($ref)
{
    $ris_map = ['article-journal' => 'JOUR', 'book' => 'BOOK', 'chapter' => 'CHAP', 'webpage' => 'ELEC'];
    $ris_type = $ris_map[$ref->type] ?? 'GEN'; // CORREÇÃO
    $output = "TY  - {$ris_type}\n";

    // CORREÇÃO: Usa -> para acessar propriedades do objeto
    if (!empty($ref->author)) {
        foreach ($ref->author as $author) {
            if (isset($author->literal)) {
                $output .= "AU  - " . $author->literal . "\n";
            } else {
                $output .= "AU  - " . ($author->family ?? '') . ', ' . ($author->given ?? '') . "\n";
            }
        }
    }

    $field_map = ['title' => 'TI', 'container-title' => 'T2', 'volume' => 'VL', 'issue' => 'IS', 'page' => 'SP', 'publisher' => 'PB', 'URL' => 'UR'];
    foreach ($field_map as $csl_key => $ris_tag) {
        // CORREÇÃO: Usa ->{} para acessar propriedades com chave variável
        if (!empty($ref->{$csl_key})) {
            $output .= "{$ris_tag}  - " . $ref->{$csl_key} . "\n";
        }
    }

    // CORREÇÃO: Usa -> para acessar propriedades do objeto
    if (!empty($ref->issued->{'date-parts'}[0][0])) {
        $output .= "PY  - " . $ref->issued->{'date-parts'}[0][0] . "\n";
    }

    $output .= "ER  - \n\n";
    return $output;
}
