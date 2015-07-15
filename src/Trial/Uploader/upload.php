<?php

$loader = require_once __DIR__ . '/../../../vendor/autoload.php';

$directoryToUpload = __DIR__ . '/../../../file_sets/set2';
$type = 'local';

// FlySystem
//echo "Uploading with FlySystem in $type...\n";
//$uploader = new \Trial\Uploader\FlySystemUploader();
//$uploader->massUpload($directoryToUpload, $type);
//echo "Done!\n";
//echo "Downloading non existent file with FlySystem...\n";
//$uploader->downloadNonExistent();
//echo "Uploading already existent file with FlySystem...\n";
//$uploader->uploadAlreadyExistent();

// Gaufrette
//echo "Uploading with Gaufrette in $type...\n";
//$uploader = new \Trial\Uploader\GaufretteUploader();
//$uploader->massUpload($directoryToUpload, $type);
//echo "Done!\n";
//echo "Downloading non existent file with Gaufrette...\n";
//$uploader->downloadNonExistent();
//echo "Uploading already existent file with Gaufrette...\n";
//$uploader->uploadAlreadyExistent();

