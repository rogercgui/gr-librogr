<?php
// Função de ajuda para converter o objeto CSL para um array plano para o formulário
function formatCSLObjectForForm($csl_ref_obj)
{
    // Converte o objeto para um array associativo para facilitar a manipulação
    $csl_ref = json_decode(json_encode($csl_ref_obj), true);

    $form_data = $csl_ref;
    if (isset($csl_ref['author']) && is_array($csl_ref['author'])) {
        $author_names = [];
        foreach ($csl_ref['author'] as $author) {
            if (isset($author['literal'])) {
                $author_names[] = '{' . $author['literal'] . '}';
            } else {
                $author_names[] = trim(($author['family'] ?? '') . ', ' . ($author['given'] ?? ''));
            }
        }
        $form_data['author'] = implode('; ', $author_names);
    }
    if (isset($csl_ref['issued']['date-parts'][0][0])) {
        $form_data['year'] = $csl_ref['issued']['date-parts'][0][0];
    }
    return $form_data;
}

$form_values = [];
if (isset($reference)) {
    $form_values = formatCSLObjectForForm($reference);
}

$current_type = $form_values['type'] ?? $_GET['type'] ?? 'article-journal';
$page_title = isset($reference) ? 'Editar Referência' : 'Nova Referência';
?>

<h2><?php echo $page_title; ?></h2>
<form action="Acoes.php" method="post" id="referenceForm">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($form_values['id'] ?? ''); ?>">
    <div>
        <label for="type">Tipo de Material</label>
        <select name="type" id="type" onchange="document.getElementById('referenceForm').submit();">
            <?php foreach (REFERENCE_TYPES as $key => $details): ?>
                <option value="<?php echo $key; ?>" <?php echo ($current_type === $key) ? 'selected' : ''; ?>>
                    <?php echo $details['label']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php
    $fields_definition = REFERENCE_TYPES[$current_type];
    $all_fields = ALL_METADATA_FIELDS;

    function render_form_field($fieldName, $fieldDetails, $currentValue)
    {
        $label = htmlspecialchars($fieldDetails['label']);
        $placeholder = isset($fieldDetails['placeholder']) ? 'placeholder="' . htmlspecialchars($fieldDetails['placeholder']) . '"' : '';
        echo "<div><label for='$fieldName'>$label</label>";
        $value_attr = 'value="' . htmlspecialchars($currentValue) . '"';
        if (in_array($fieldName, ['note', 'author'])) {
            echo "<textarea name='data[$fieldName]' id='$fieldName' $placeholder>" . htmlspecialchars($currentValue) . "</textarea>";
        } else {
            echo "<input type='text' name='data[$fieldName]' id='$fieldName' $value_attr $placeholder>";
        }
        echo "</div>";
    }

    // Renderiza campos
    render_form_field('citekey', $all_fields['citekey'], $form_values['citekey'] ?? '');
    echo "<h3>Campos Principais</h3>";
    foreach ($fields_definition['required'] as $field) {
        render_form_field($field, $all_fields[$field], $form_values[$field] ?? '');
    }
    echo "<h3>Campos Opcionais</h3>";
    foreach ($fields_definition['optional'] as $field) {
        render_form_field($field, $all_fields[$field], $form_values[$field] ?? '');
    }
    ?>

    <h3>Anotações Pessoais</h3>
    <?php
    // Pega as anotações existentes se estiver editando
    $current_annotations = [];
    if (isset($reference)) {
        $current_annotations = getAnnotationByRefId($reference->id);
    }
    ?>
    <div>
        <label for="notas">Notas (Lembretes, ideias, etc.)</label>
        <textarea name="annotations[notas]" id="notas"><?php echo htmlspecialchars($current_annotations['notas'] ?? ''); ?></textarea>
    </div>
    <div>
        <label for="resumo">Resumo</label>
        <textarea name="annotations[resumo]" id="resumo" style="min-height: 120px;"><?php echo htmlspecialchars($current_annotations['resumo'] ?? ''); ?></textarea>
    </div>

    <div class="form-actions">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>

<div class="form-actions">
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Salvar</button>
</div>
</form>