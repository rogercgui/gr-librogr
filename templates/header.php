<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Referências</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container-fluid">
        <header>
            <h1><a href="index.php">Gerenciador de Referências PHP</a></h1>
            <style type="text/css">
                <?php echo $csl_styles_css; ?>
            </style>

            <div class="actions">
                <div class="dropdown">
                    <button class="btn btn-primary">Inserir Nova Referência</button>
                    <div class="dropdown-content">
                        <?php foreach (REFERENCE_TYPES as $key => $details): ?>
                            <a href="index.php?action=new&type=<?php echo $key; ?>"><?php echo $details['label']; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form action="index.php" method="get" class="search-form">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="q" placeholder="Pesquisar..." required>
                    <button type="submit" class="btn btn-secondary">Buscar</button>
                </form>
                <a href="import.php" class="btn btn-secondary" style="display: inline-block; margin-left: 10px;">Importar Arquivo</a>

            </div>

        </header>
        <main>