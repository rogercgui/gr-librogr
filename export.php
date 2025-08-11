<?php
/*
require_once 'config.php';
$id = $_GET['id'] ?? null;
$format = $_GET['format'] ?? 'bibtex';

if (!$id) die('ID da referência não fornecido.');

$ref = getReferenceById($id);
if (!$ref) die('Referência não encontrada.');

if ($format === 'bibtex') {
    header('Content-Type: application/x-bibtex');
    header('Content-Disposition: attachment; filename="' . ($ref['citekey'] ?? $ref['id']) . '.bib"');

    $type = $ref['type']; // 'article', 'book', etc.
    $citekey = $ref['citekey'] ?? $ref['id'];

    echo "@{$type}{{$citekey},\n";
    foreach ($ref as $key => $value) {
        // Ignora campos internos
        if (in_array($key, ['id', 'type', 'timestamp', 'citekey'])) continue;
        
        // Formata autores para o padrão BibTeX
        if ($key === 'author' || $key === 'editor') {
            $value = str_replace(';', ' and', $value);
        }
        
        echo "  {$key} = {{" . $value . "}},\n";
    }
    echo "}\n";
} 
// Adicionar lógica para 'ris' e 'biblatex' aqui...
*/