<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\tests\helper\EntityBuilder;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProduct;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * Test product models and their descendants have been correctly indexed after being saved.
 */
class IndexingProductModelDescendantsIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testIndexingProductModelDescendantsOnUnitarySave()
    {
        $this->createProductsAndProductModelsTree('seed');

        $this->get('doctrine.orm.entity_manager')->clear();

        $rootProductModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('seed_root_product_model');

        $this->get('pim_catalog.updater.product_model')->update($rootProductModel, [
            'values' => [
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
            ],
        ]);

        $this->get('pim_catalog.saver.product_model')->save($rootProductModel);

        sleep(10);

        $this->assertDocumentIdsForSearch(
            [
                'seed_root_product_model',
                'seed_sub_product_model_1',
                'seed_sub_product_model_2',
                'seed_variant_product_1',
                'seed_variant_product_2',
                'seed_variant_product_3',
                'seed_variant_product_4',
            ],
            [
                '_source' => 'identifier',
                'query'   => [
                    'bool' => [
                        'filter' => [
                            'exists' => [
                                'field' => 'values.a_date-date.<all_channels>.<all_locales>',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testIndexingProductModelsDescendantsOnBulkSave()
    {
        $this->createProductsAndProductModelsTree('seed1');
        $this->createProductsAndProductModelsTree('seed2');

        $this->get('doctrine.orm.entity_manager')->clear();

        $rootProductModel1 = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('seed1_root_product_model');
        $rootProductModel2 = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('seed2_root_product_model');

        $this->get('pim_catalog.updater.product_model')->update($rootProductModel1, [
            'values' => [
                'a_date' => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13T00:00:00+02:00'],
                ],
            ],
        ]);

        $this->get('pim_catalog.updater.product_model')->update($rootProductModel2, [
            'values' => [
                'a_file' => [
                    ['locale' => null, 'scope' => null, 'data' => $this->getFixturePath('akeneo.txt')],
                ],
            ],
        ]);

        $this->get('pim_catalog.saver.product_model')->saveAll([$rootProductModel1, $rootProductModel2]);

        sleep(10);

        $this->assertDocumentIdsForSearch(
            [
                'seed1_root_product_model',
                'seed1_sub_product_model_1',
                'seed1_sub_product_model_2',
                'seed1_variant_product_1',
                'seed1_variant_product_2',
                'seed1_variant_product_3',
                'seed1_variant_product_4',
            ],
            [
                '_source' => 'identifier',
                'query'   => [
                    'bool' => [
                        'filter' => [
                            'exists' => [
                                'field' => 'values.a_date-date.<all_channels>.<all_locales>',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }

    /**
     * Checks the expected identifier code are returned by the ES search.
     *
     * @param array $expectedIdentifiers
     * @param array $search
     *
     * @return bool
     */
    private function assertDocumentIdsForSearch(array $expectedIdentifiers, array $search): bool
    {
        $documents = $this->esProductAndProductModelClient->search(self::DOCUMENT_TYPE, $search);
        $actualDocumentIdentifiers = array_map(function ($document) {
            return $document['_source']['identifier'];
        }, $documents['hits']['hits']);

        sort($expectedIdentifiers);
        sort($actualDocumentIdentifiers);

        $this->assertSame($expectedIdentifiers, $actualDocumentIdentifiers);

        return true;
    }

    /**
     * Creates some products and product models related to each other within the same familyVariant
     *
     * @param string $seed
     */
    private function createProductsAndProductModelsTree(string $seed)
    {
        $entityBuilder = new EntityBuilder(static::$kernel->getContainer());

        $rootProductModel = $entityBuilder->createProductModel($seed . '_root_product_model', 'familyVariantA1', null, []);

        $subProductModel1 = $entityBuilder->createProductModel($seed . '_sub_product_model_1', 'familyVariantA1', $rootProductModel, []);
        $subProductModel2 = $entityBuilder->createProductModel($seed . '_sub_product_model_2', 'familyVariantA1', $rootProductModel, []);

        $variantProduct1 = $entityBuilder->createVariantProduct($seed . '_variant_product_1', 'familyA', 'familyVariantA1', $subProductModel1, []);
        $variantProduct2 = $entityBuilder->createVariantProduct($seed . '_variant_product_2', 'familyA', 'familyVariantA1', $subProductModel1, []);
        $variantProduct3 = $entityBuilder->createVariantProduct($seed . '_variant_product_3', 'familyA', 'familyVariantA1', $subProductModel2, []);
        $variantProduct4 = $entityBuilder->createVariantProduct($seed . '_variant_product_4', 'familyA', 'familyVariantA1', $subProductModel2, []);

        $this->get('pim_catalog.saver.product_model')->save($rootProductModel);
        $this->get('pim_catalog.saver.product_model')->saveAll([$subProductModel1, $subProductModel2]);
        $this->get('pim_catalog.saver.product')->saveAll([
            $variantProduct1,
            $variantProduct2,
            $variantProduct3,
            $variantProduct4,
        ]);
    }
}
