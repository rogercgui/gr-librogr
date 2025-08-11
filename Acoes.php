<?php
require_once 'config.php';

/**
 * Converte os dados planos recebidos de um formulário para o formato CSL-JSON.
 * @param array $flat_data Dados do $_POST['data'].
 * @param string $type O tipo de referência (ex: 'article-journal').
 * @return object A referência formatada como objeto CSL-JSON.
 */
function parseDataToCSLJSON(array $flat_data, string $type)
{
    $csl_data = [];
    foreach ($flat_data as $key => $value) {
        if (!empty($value)) {
            $csl_data[$key] = $value;
        }
    }

    $csl_data['type'] = $type;

    if (isset($flat_data['author'])) {
        $authors = [];
        $author_list = explode(';', $flat_data['author']);
        foreach ($author_list as $author_str) {
            $author_str = trim($author_str);
            if (empty($author_str)) continue;

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
        $csl_data['author'] = $authors;
    }

    if (isset($flat_data['year'])) {
        $date_object = new stdClass();
        $date_object->{'date-parts'} = [[$flat_data['year']]];
        $csl_data['issued'] = $date_object;
        unset($csl_data['year']);
    }

    $field_map = ['journal' => 'container-title', 'number' => 'issue', 'pages' => 'page'];
    foreach ($field_map as $form_key => $csl_key) {
        if (isset($csl_data[$form_key])) {
            $csl_data[$csl_key] = $csl_data[$form_key];
            unset($csl_data[$form_key]);
        }
    }

    return (object) $csl_data;
}

// --- LÓGICA PRINCIPAL DE PROCESSAMENTO ---

// Define a URL de redirecionamento padrão
$redirect_url = 'index.php';

// Lida com ações POST do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'save') {
        $id = $_POST['id'];
        $type = $_POST['type'];
        $data = $_POST['data'];
        $annotations_data = $_POST['annotations'] ?? null; // Pega os dados das anotações


        $references = getReferences();
        $reference_data = parseDataToCSLJSON($data, $type);
        $target_id = null;

        if (empty($id)) { // Criando nova referência
            $new_id = uniqid('ref_', true);
            $reference_data->id = $new_id;
            $reference_data->timestamp = time();
            $references[] = $reference_data;
            $target_id = $new_id;
        } else { // Atualizando referência existente
            foreach ($references as $key => $ref) {
                if ($ref->id === $id) {
                    $timestamp = $ref->timestamp;
                    $references[$key] = $reference_data;
                    $references[$key]->id = $id;
                    $references[$key]->timestamp = $timestamp;
                    break;
                }
            }
            $target_id = $id;
        }

        saveReferences($references);

        // Lida com as anotações se os dados forem fornecidos
        if ($target_id && $annotations_data) {
            $annotations = getAnnotations();
            $notas = trim($annotations_data['notas']);
            $resumo = trim($annotations_data['resumo']);

            // Se ambos estiverem vazios, remove a anotação. Senão, salva.
            if (empty($notas) && empty($resumo)) {
                unset($annotations[$target_id]);
            } else {
                $annotations[$target_id] = [
                    'notas' => $notas,
                    'resumo' => $resumo
                ];
            }
            saveAnnotations($annotations);
        }

        // Se tivermos um ID de destino, mudamos a URL de redirecionamento
        if ($target_id) {
            $redirect_url = 'index.php?action=view&id=' . $target_id;
        }
    }
}

// Lida com ações GET (como apagar)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if ($action === 'delete') {
        $id = $_GET['id'];
        $references = getReferences();
        $references_updated = array_filter($references, function ($ref) use ($id) {
            return $ref->id !== $id;
        });
        saveReferences(array_values($references_updated));
        // Para a ação de apagar, o redirecionamento padrão para 'index.php' é o correto.
    }
}

// Redireciona para a URL definida
header('Location: ' . $redirect_url);
exit();
