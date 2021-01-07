<?php

if (!file_exists(__DIR__ . '/../../app/AppKernel.php')) {
    die("Please run this command from your Symfony application root.");
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/AppKernel.php';
require __DIR__ . '/CleanIndex.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv(true))->load($envFile);
}

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$builder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];
$eeDir = $container->getParameter('pim_ee_dev_src_folder_location');
$index = $container->getParameter('record_index_name');
$configurationLoader = new Loader(
    [
        "{$eeDir}/src/Akeneo/ReferenceEntity/back/Infrastructure/Symfony/Resources/config/search/record_index_configuration.yml",
    ]
);

echo "Creating '$index' index...\n";

$client = new Client($builder, $configurationLoader, $hosts, $index);
$client->createIndex();

echo "Done.\n";
