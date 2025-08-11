<?php

/**
 * Funções de ajuda para o processamento de CSL.
 */

function get_csl_locale($csl_file_path)
{
    if (!file_exists($csl_file_path)) return 'en-US';
    $xml = @simplexml_load_file($csl_file_path);
    if ($xml && isset($xml->locale['xml:lang'])) {
        return (string) $xml->locale['xml:lang'];
    }
    return 'en-US';
}

function getAvailableStyles()
{
    $styles = [];
    $path = __DIR__ . '/refmodels/';
    if (!is_dir($path)) return [];
    $files = scandir($path);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'csl') {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_file($path . $file);
            if ($xml !== false && isset($xml->info->title)) {
                $title = (string)$xml->info->title;
                $styles[$file] = $title;
            }
            libxml_clear_errors();
        }
    }
    return $styles;
}

function convertToCSLJSON($ref)
{
    return (object) $ref;
}
