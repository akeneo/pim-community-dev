<?php

require_once __DIR__ . '/FileSetGenerator.php';
require_once __DIR__ . '/FileGenerator.php';

echo "Generating file set...\n";
$generator = new \Trial\FileGenerator\FileSetGenerator();
$log = $generator->generate(0, 0, 1);
echo "Done! $log\n";

echo "Generating files...\n";
$generator = new \Trial\FileGenerator\FileGenerator();
$generator->generate($log);
echo "Done!\n";
