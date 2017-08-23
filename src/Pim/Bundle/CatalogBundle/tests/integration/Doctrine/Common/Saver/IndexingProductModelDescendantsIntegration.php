<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
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
                'seed_product_variant_1',
                'seed_product_variant_2',
                'seed_product_variant_3',
                'seed_product_variant_4',
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
                'seed1_product_variant_1',
                'seed1_product_variant_2',
                'seed1_product_variant_3',
                'seed1_product_variant_4',
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
        $rootProductModel = $this->createProductModel($seed . '_root_product_model', null);

        $subProductModel1 = $this->createProductModel($seed . '_sub_product_model_1', $rootProductModel);
        $subProductModel2 = $this->createProductModel($seed . '_sub_product_model_2', $rootProductModel);

        $variantProduct1 = $this->createVariantProduct($seed . '_product_variant_1', $subProductModel1);
        $variantProduct2 = $this->createVariantProduct($seed . '_product_variant_2', $subProductModel1);
        $variantProduct3 = $this->createVariantProduct($seed . '_product_variant_3', $subProductModel2);
        $variantProduct4 = $this->createVariantProduct($seed . '_product_variant_4', $subProductModel2);

        $this->get('pim_catalog.saver.product_model')->save($rootProductModel);
        $this->get('pim_catalog.saver.product_model')->saveAll([$subProductModel1, $subProductModel2]);
        $this->get('pim_catalog.saver.product')->saveAll([
            $variantProduct1,
            $variantProduct2,
            $variantProduct3,
            $variantProduct4,
        ]);
    }

    /**
     * @param string                     $identifier
     * @param null|ProductModelInterface $parent
     *
     * @return ProductModelInterface
     */
    private function createProductModel(
        string $identifier,
        $parent
    ): ProductModelInterface {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();

        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code'           => $identifier,
            'family_variant' => 'familyVariantA1',
        ]);

        if (null !== $parent) {
            $productModel->setParent($parent);
        }

        return $productModel;
    }

    /**
     * TODO: use the factory/builder of variant products when it exists
     *
     * Creates a variant product with identifier and product model parent
     *
     * @param string                $identifier
     * @param ProductModelInterface $parent
     *
     * @return VariantProductInterface
     */
    private function createVariantProduct(string $identifier, ProductModelInterface $parent): VariantProductInterface
    {
        $variantProduct = new VariantProduct();

        $identifierAttribute = $this->get('pim_catalog.repository.attribute')->findOneByCode('sku');

        $entityWithValuesBuilder = $this->get('pim_catalog.builder.entity_with_values');
        $entityWithValuesBuilder->addOrReplaceValue($variantProduct, $identifierAttribute, null, null, $identifier);

        $this->get('pim_catalog.updater.product')->update(
            $variantProduct,
            [
                'family' => 'familyA',
            ]
        );

        $variantProduct->setParent($parent);

        $familyVariant = $this->get('pim_catalog.repository.family_variant')->findOneByCode('familyVariantA1');
        $variantProduct->setFamilyVariant($familyVariant);

        return $variantProduct;
    }
}
