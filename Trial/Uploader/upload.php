<?php

$loader = require_once __DIR__ . '/../../../vendor/autoload.php';

$directoryToUpload = __DIR__ . '/../../../file_sets/set2';
$type = 'local';

// FlySystem
echo "Uploading with FlySystem in $type...\n";
$uploader = new \Akeneo\Trial\Uploader\FlySystemUploader();
$uploader->massUpload($directoryToUpload, $type);
echo "Done!\n";

// Gaufrette
//echo "Uploading with Gaufrette in $type...\n";
//$uploader = new \Akeneo\Trial\Uploader\GaufretteUploader();
//$uploader->massUpload($directoryToUpload, $type);
//echo "Done!\n";
