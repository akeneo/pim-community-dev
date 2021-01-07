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

$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Pim\Upgrade\Schema\CleanIndex;
use Pim\Upgrade\Schema\IndexMovement;

$builder = $container->get('akeneo_elasticsearch.client_builder');
$hosts = [$container->getParameter('index_hosts')];
$ceDir = $container->getParameter('pim_ce_dev_src_folder_location');
$eeDir = $container->getParameter('pim_ee_dev_src_folder_location');
$cleanIndex = new CleanIndex();

$productMappingConfigurationLoader = new Loader(
    [
        "{$ceDir}/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/settings.yml",
        "{$ceDir}/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/product_mapping.yml",
    ]
);

$publishedProductMappingConfigurationLoader = new Loader(
    [
        "{$ceDir}/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/settings.yml",
        "{$eeDir}/src/Akeneo/Pim/WorkOrganization/Workflow/Bundle/Resources/elasticsearch/published_product_mapping.yml",

    ]
);

$productProposalMappingConfigurationLoader = new Loader(
    [
        "{$ceDir}/src/Akeneo/Pim/Enrichment/Bundle/Resources/elasticsearch/settings.yml",
        "{$eeDir}/src/Akeneo/Pim/WorkOrganization/Workflow/Bundle/Resources/elasticsearch/product_proposal_mapping.yml"
    ]
);

$productAndProductModelType = 'pim_catalog_product';

$cleanProductIndexPimCatalogProduct = new IndexMovement($productMappingConfigurationLoader, $container->getParameter("product_index_name"), $container->getParameter("product_index_name"), $productAndProductModelType);
$cleanProductModelIndexPimCatalogProduct = new IndexMovement($productMappingConfigurationLoader, $container->getParameter("product_model_index_name"), $container->getParameter("product_model_index_name"), $productAndProductModelType);
$cleanProductAndProductModelIndexPimCatalogProduct = new IndexMovement($productMappingConfigurationLoader, $container->getParameter("product_and_product_model_index_name"), $container->getParameter("product_and_product_model_index_name"), $productAndProductModelType);

$cleanProductIndexPimeeWorkflowPublishedProduct = new IndexMovement($publishedProductMappingConfigurationLoader, $container->getParameter("product_index_name"), $container->getParameter("published_product_index_name"), "pimee_workflow_published_product");
$cleanProductAndProductModelIndexPimeeWorkflowPublishedProduct = new IndexMovement($publishedProductMappingConfigurationLoader, $container->getParameter("product_and_product_model_index_name"), $container->getParameter("published_product_and_product_model_index_name"), "pimee_workflow_published_product");

$cleanProductProposalIndex = new IndexMovement($productProposalMappingConfigurationLoader, $container->getParameter("product_proposal_index_name"), $container->getParameter("product_proposal_index_name"), "pimee_workflow_product_proposal");

echo "Starting Elasticsearch indices cleaning \n";

$cleanIndex(
    $builder,
    $hosts,
    [
        $cleanProductIndexPimCatalogProduct,
        $cleanProductModelIndexPimCatalogProduct,
        $cleanProductAndProductModelIndexPimCatalogProduct,
        $cleanProductIndexPimeeWorkflowPublishedProduct,
        $cleanProductAndProductModelIndexPimeeWorkflowPublishedProduct,
        $cleanProductProposalIndex,
    ]
);

echo "Done.\n";
