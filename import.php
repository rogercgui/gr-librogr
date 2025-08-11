<?php
include 'templates/header.php';
?>

<h2>Importar Referências</h2>
<p>Envie um arquivo nos formatos BibTeX (.bib), BibLaTeX (.bib) ou RIS (.ris) para adicionar múltiplas referências de uma só vez.</p>
<p><strong>Atenção:</strong> Atualmente, o parser está otimizado para BibTeX/BibLaTeX.</p>

<form action="AcoesImportar.php" method="post" enctype="multipart/form-data">
    <div>
        <label for="reference_file">Selecione o arquivo:</label>
        <input type="file" name="reference_file" id="reference_file" required accept=".bib,.ris,.txt">
    </div>

    <div class="form-actions">
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Importar</button>
    </div>
</form>

<?php
include 'templates/footer.php';
?>