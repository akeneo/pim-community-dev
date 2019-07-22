<?php

// This script is used to relocate import/export logs from a local filesystem to an object storage.
//
// The log files are listed from the "akeneo_batch_job_execution" table.
// Then, each existing and readable local file is stored in the object storage "oneup_flysystem.archivist_filesystem".
//
// Please note that this script does NOT delete source files.

if (!file_exists(__DIR__ . '/../../app/AppKernel.php')) {
    die("Please run this command from your Symfony application root.");
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/AppKernel.php';
require __DIR__ . '/LocalLogToObjectStorage.php';
require __DIR__ . '/RelocateException.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->load($envFile);
}

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$storage = new \Pim\Upgrade\Schema\LocalLogToObjectStorage(
    $container->get('oneup_flysystem.archivist_filesystem'),
    $container->get('doctrine.dbal.default_connection')
);

echo "Relocating {$storage->countFiles()} local import/export logs to the object storage...\n";

$warnings = $storage->relocateFiles();
$relocated = $storage->countRelocated();

if (0 === $relocated) {
    echo "No file have been relocated!\nDone!\n";
    exit(-1);
}

if (!empty($warnings)) {
    echo "The following files have NOT been relocated:\n";
    foreach ($warnings as $warning) {
        echo "- $warning\n";
    }
}

echo "Done!\n";
