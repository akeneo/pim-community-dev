<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadProductAndProductModelIntegration extends TestCase
{
    public function test_product_is_not_dirty_after_fetching_it_from_database()
    {
        $this->createProduct(
            'baz',
            [
                'family' => 'familyA',
                'values' => [
                    'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
                    'a_text' => [['locale' => null, 'scope' => null, 'data' => 'Lorem ipsum dolor sit amet']],
                ],
                'groups' => ['groupA'],
                'categories' => ['categoryA'],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['foo'],
                        'product_models' => ['bar'],
                        'groups' => ['groupB'],
                    ],
                    'TWOWAY' => [
                        'products' => ['foo'],
                        'product_models' => ['bar'],
                    ]
                ],
                'quantified_associations' => [
                    'QUANTIFIED' => [
                        'products' => [
                            [
                                'identifier' => 'foo',
                                'quantity' => 2,
                            ],
                        ],
                        'product_models' => [
                            [
                                'identifier' => 'bar',
                                'quantity' => 5,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $baz = $this->get('pim_catalog.repository.product')->findOneByIdentifier('baz');
        Assert::assertFalse($baz->isDirty(), 'The product should not be dirty after loading it from the database');
    }

    public function test_product_model_is_not_dirty_after_fetching_it_from_database()
    {
        $this->createProductModel(
            [
                'code' => 'baz',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_number_integer' => [['locale' => null, 'scope' => null, 'data' => 42]],
                    'a_multi_select' => [['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']]],
                ],
                'categories' => ['categoryA'],
                'associations' => [
                    'X_SELL' => [
                        'products' => ['foo'],
                        'product_models' => ['bar'],
                        'groups' => ['groupB'],
                    ],
                    'TWOWAY' => [
                        'products' => ['foo'],
                        'product_models' => ['bar'],
                    ]
                ],
                'quantified_associations' => [
                    'QUANTIFIED' => [
                        'products' => [
                            [
                                'identifier' => 'foo',
                                'quantity' => 2,
                            ],
                        ],
                        'product_models' => [
                            [
                                'identifier' => 'bar',
                                'quantity' => 5,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $baz = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('baz');
        Assert::assertFalse($baz->isDirty(), 'The product model should not be dirty after loading it from the database');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // create a 2-way association type
        $twoWayAssociationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $twoWayAssociationType,
            [
                'code' => 'TWOWAY',
                'is_two_way' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($twoWayAssociationType);

        // create a quantified association type
        $quantifiedAssociationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $quantifiedAssociationType,
            [
                'code' => 'QUANTIFIED',
                'is_quantified' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($quantifiedAssociationType);

        $this->createProduct('foo', []);
        $this->createProductModel(
            [
                'code' => 'bar',
                'family_variant' => 'familyVariantA1',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertCount(0, $violations, \sprintf('The product is not valid: %s', (string)$violations));
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is not valid: %s', (string)$violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
