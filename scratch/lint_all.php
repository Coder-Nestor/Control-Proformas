<?php

$dir = __DIR__ . '/..';
$folders = ['app', 'core', 'config', 'public'];

$errorCount = 0;
$checkedCount = 0;

foreach ($folders as $folder) {
    $path = $dir . '/' . $folder;
    if (!is_dir($path)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $checkedCount++;
            $filePath = $file->getRealPath();
            $output = [];
            $exitCode = 0;
            exec("php -l \"$filePath\" 2>&1", $output, $exitCode);
            if ($exitCode !== 0) {
                echo "SYNTAX ERROR in $filePath:\n" . implode("\n", $output) . "\n\n";
                $errorCount++;
            }
        }
    }
}

echo "Checked $checkedCount PHP files.\n";
if ($errorCount === 0) {
    echo "ALL FILES PASSED SYNTAX CHECK!\n";
} else {
    echo "$errorCount files failed syntax check!\n";
}
