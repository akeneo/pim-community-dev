<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\PublishedProduct;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

class ExportPublishedVariantProductsEndToEnd extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $jobInstance = new JobInstance('Akeneo CSV Connector', 'export', 'csv_published_product_export');
        $jobInstance->setCode('csv_published_product_export');
        $jobInstance->setLabel('Published CSV product export');
        $this->get('akeneo_batch.saver.job_instance')->save($jobInstance);

        $this->jobLauncher       = new JobLauncher(static::$kernel);
        $publishedProductManager = $this->get('pimee_workflow.manager.published_product');

        $product = $this->get('pimee_security.repository.product')->findOneByIdentifier('1111111113');
        $publishedProductManager->publish($product, ['flush' => true]);

        $clientRegistry = $this->get('akeneo_elasticsearch.registry.clients');
        $clients = $clientRegistry->getClients();
        foreach ($clients as $client) {
            $client->refreshIndex();
        }
        $this->get('doctrine')->getManager()->clear();
    }

    public function testPublishedVariantProduct()
    {
        $expectedCsv = <<<CSV
sku;categories;enabled;family;parent;groups;brand;care_instructions;collection;color;composition;description-de_DE-mobile;description-en_US-mobile;description-fr_FR-mobile;ean;erp_name-de_DE;erp_name-en_US;erp_name-fr_FR;image;keywords-de_DE;keywords-en_US;keywords-fr_FR;material;meta_description-de_DE;meta_description-en_US;meta_description-fr_FR;meta_title-de_DE;meta_title-en_US;meta_title-fr_FR;name-de_DE;name-en_US;name-fr_FR;notice;price-EUR;price-USD;size;supplier;variation_image;variation_name-de_DE;variation_name-en_US;variation_name-fr_FR;wash_temperature;weight;weight-unit
1111111113;master_men_blazers,supplier_zaro;1;clothing;amor;;;;summer_2016;blue;;;;;1234567890125;;Amor;;;;;;;;;;;;;;"Heritage jacket navy";;;999.00;;m;zaro;;;;;800;;

CSV;

        $config = [
            'filters' => [
                'data'      => [],
                'structure' => [
                    'scope'   => 'mobile',
                    'locales' => ['en_US', 'fr_FR', 'de_DE'],
                ],
            ],
        ];

        $csv = $this->jobLauncher->launchAuthenticatedExport('csv_published_product_export', 'julia', $config);
        $this->assertSame($expectedCsv, $csv);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
