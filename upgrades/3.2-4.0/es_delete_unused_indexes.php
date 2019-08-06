<?php

if (!file_exists(__DIR__ . '/../../app/AppKernel.php')) {
    die("Please run this command from your Symfony application root.");
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../app/AppKernel.php';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    (new Symfony\Component\Dotenv\Dotenv())->load($envFile);
}

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

$builder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];

$client = $builder->setHosts($hosts)->build()->indices();

function deleteAliasAndIndex($client, $aliasName) {
    if ($client->existsAlias(['name' => $aliasName ])) {
        echo sprintf("Deleting alias \"%s\" and index.\n", $aliasName);
        $alias = $client->getAlias(['name' => $aliasName ]);
        $index = array_keys($alias)[0];
        $client->deleteAlias([ 'index' => $index, 'name' => $aliasName ]);
        return $client->delete(['index' => $index]);
    }

    echo sprintf(
        "The alias \"%s\" does not exist. This could be because it's part of the Enterprise Edition.\n",
        $aliasName
    );
}

deleteAliasAndIndex($client, 'akeneo_pim_product');
deleteAliasAndIndex($client, 'akeneo_pim_product_model');
deleteAliasAndIndex($client, 'akeneo_pim_published_product_and_product_model');

echo "Done.\n";
