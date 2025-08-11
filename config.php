<?php

$model_default = 'apa-numeric-superscript';

date_default_timezone_set('America/Sao_Paulo');


// --- CONFIGURAÇÕES GERAIS ---
define('DATA_FILE', __DIR__ . '/data/references.json');

// Define um estilo CSL padrão. Deixe em branco ('') para usar a visualização simples.
define('DEFAULT_CSL_STYLE', $model_default.'.csl');

// --- ESTRUTURA DOS FORMULÁRIOS ---
define('ALL_METADATA_FIELDS', [
    'type'        => ['label' => 'Tipo de Material'],
    'citekey'     => ['label' => 'Chave de Citação (Ex: autor_ano)'],
    'author'      => ['label' => 'Autor(es)', 'placeholder' => 'Sobrenome, Nome; Sobrenome, Nome'],
    'editor'      => ['label' => 'Editor(es)', 'placeholder' => 'Sobrenome, Nome; Sobrenome, Nome'],
    'title'       => ['label' => 'Título'],
    'booktitle'   => ['label' => 'Título do Livro (p/ capítulos)'],
    'container-title' => ['label' => 'Periódico/Revista'],
    'year'        => ['label' => 'Ano'],
    'volume'      => ['label' => 'Volume'],
    'issue'       => ['label' => 'Número/Edição'],
    'page'        => ['label' => 'Páginas', 'placeholder' => 'Ex: 15-25'],
    'publisher'   => ['label' => 'Editora'],
    'publisher-place' => ['label' => 'Local de Publicação'],
    'URL'         => ['label' => 'URL'],
    'urldate'     => ['label' => 'Data de Acesso', 'placeholder' => 'YYYY-MM-DD'],
    'note'        => ['label' => 'Nota'],
]);

define('REFERENCE_TYPES', [
    'article-journal' => [
        'label' => 'Artigo de Periódico',
        'required' => ['author', 'title', 'container-title', 'year','URL'],
        'optional' => ['volume', 'issue', 'page', 'note']
    ],
    'book' => [
        'label' => 'Livro',
        'required' => ['author', 'title', 'publisher', 'year'],
        'optional' => ['volume', 'publisher-place', 'note']
    ],
    'chapter' => [
        'label' => 'Capítulo de Livro',
        'required' => ['author', 'title', 'booktitle', 'publisher', 'year'],
        'optional' => ['editor', 'volume', 'page', 'publisher-place', 'note']
    ],
    'webpage' => [
        'label' => 'Site',
        'required' => ['author', 'title', 'year', 'URL'],
        'optional' => ['urldate', 'note']
    ]
]);

// --- FUNÇÕES DE AJUDA ---
// Em config.php, substitua a função getReferences por esta:
function getReferences()
{
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $json_data = file_get_contents(DATA_FILE);
    // Remove o ", true" para decodificar como um array de objetos (stdClass)
    return json_decode($json_data) ?: [];
}

function saveReferences($references)
{
    usort($references, function ($a, $b) {
        // CORREÇÃO: Usa ->timestamp para acessar a propriedade do objeto
        $timestamp_a = $a->timestamp ?? 0;
        $timestamp_b = $b->timestamp ?? 0;
        return $timestamp_b <=> $timestamp_a;
    });
    $json_data = json_encode($references, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(DATA_FILE, $json_data);
}

function getReferenceById($id)
{
    $references = getReferences();
    foreach ($references as $ref) {
        // CORREÇÃO: de $ref['id'] para $ref->id
        if ($ref->id === $id) {
            return $ref;
        }
    }
    return null;
}


// --- FUNÇÕES DE AJUDA PARA ANOTAÇÕES ---

define('ANNOTATIONS_FILE', __DIR__ . '/data/annotations.json');

function getAnnotations()
{
    if (!file_exists(ANNOTATIONS_FILE)) return [];
    $json_data = file_get_contents(ANNOTATIONS_FILE);
    return json_decode($json_data, true) ?: [];
}

function saveAnnotations($annotations)
{
    $json_data = json_encode($annotations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(ANNOTATIONS_FILE, $json_data);
}

function getAnnotationByRefId($ref_id)
{
    $annotations = getAnnotations();
    return $annotations[$ref_id] ?? ['notas' => '', 'resumo' => ''];
}