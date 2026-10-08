<?php

// Standalone, dependency-free extractor for a manually-FTP-uploaded
// app-bundle.zip -- deliberately does NOT touch vendor/autoload.php or
// bootstrap Laravel, because on a brand new deploy vendor/ doesn't exist
// yet (that's exactly what this script's job is to create), so an artisan
// command can't be the thing that does this.
//
// Meant to live at app/extract-bundle.php, next to where app-bundle.zip
// gets uploaded (see deploy/README-copperfitting.md). Safe to run
// repeatedly/on every cron tick: if there's no zip waiting, it does
// nothing.

$zipPath = __DIR__.'/app-bundle.zip';

if (! file_exists($zipPath)) {
    echo "No app-bundle.zip waiting -- nothing to extract.\n";
    exit(0);
}

if (! class_exists('ZipArchive')) {
    fwrite(STDERR, "PHP's zip extension isn't available -- can't extract app-bundle.zip.\n");
    exit(1);
}

$zip = new ZipArchive;

$result = $zip->open($zipPath);

if ($result !== true) {
    fwrite(STDERR, "Failed to open app-bundle.zip (ZipArchive error code {$result}).\n");
    exit(1);
}

echo "Extracting app-bundle.zip ({$zip->numFiles} files) into ".__DIR__."...\n";

if (! $zip->extractTo(__DIR__)) {
    fwrite(STDERR, "Extraction failed.\n");
    $zip->close();
    exit(1);
}

$zip->close();
unlink($zipPath);

echo "Done -- app-bundle.zip extracted and removed.\n";
