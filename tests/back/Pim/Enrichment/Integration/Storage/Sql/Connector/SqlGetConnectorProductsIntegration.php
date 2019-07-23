<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetConnectorProductsIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProductModel(
            [
                'code' => 'amor',
                'family_variant' => 'familyVariantA2',
                'categories' => ['categoryA2'],
                'values'  => [
                    'a_price'  => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area'  => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => ['groupA']
                    ]
                ]
            ]
        );

        $this->createVariantProduct('apollon_A_false', [
            'categories' => ['categoryB', 'categoryC'],
            'parent' => 'amor',
            'values' => [
                'a_simple_select' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'optionA',
                    ],
                ],
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => false,
                    ],
                ],
            ],
        ]);

        $this->createVariantProduct('apollon_B_false', [
            'categories' => ['categoryA1'],
            'parent' => 'amor',
            'groups' => ['groupA', 'groupB'],
            'values' => [
                'a_simple_select' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'optionB',
                    ],
                ],
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => false,
                    ],
                ],
            ],
            'associations' => [
                'X_SELL' => [
                    'products' => ['apollon_A_false'],
                    'product_models' => ['amor'],
                    'groups' => ['groupB']
                ]
            ]
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @group ce
     */
    public function test_get_product_from_the_PQB()
    {
        $query = $this->getQuery();
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create(['limit' => 10]);

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $product = $query->fromProductQueryBuilder($pqb, (int) $userId, null, null, null);

        $productDataAppolonA = $this->get('database_connection')->executeQuery(
            'SELECT id, created, updated FROM pim_catalog_product WHERE identifier = "apollon_A_false"'
        )->fetch();
        $productDataAppolonB = $this->get('database_connection')->executeQuery(
            'SELECT id, created, updated FROM pim_catalog_product WHERE identifier = "apollon_B_false"'
        )->fetch();


        $expectedProducts = new ConnectorProductList(2, [
            new ConnectorProduct(
                (int) $productDataAppolonA['id'],
                'apollon_A_false',
                new \DateTimeImmutable($productDataAppolonA['created']),
                new \DateTimeImmutable($productDataAppolonA['updated']),
                true,
                'familyA',
                ['categoryA2', 'categoryB', 'categoryC'],
                [],
                'amor',
                [
                    'X_SELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => ['groupA'],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ]
                ],
                [],
                new ReadValueCollection([
                    OptionValue::value('a_simple_select', 'optionA'),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    ScalarValue::value('a_yes_no', false),
                    ScalarValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ])
            ),
            new ConnectorProduct(
                (int) $productDataAppolonB['id'],
                'apollon_B_false',
                new \DateTimeImmutable($productDataAppolonB['created']),
                new \DateTimeImmutable($productDataAppolonB['updated']),
                true,
                'familyA',
                ['categoryA1', 'categoryA2'],
                ['groupA', 'groupB'],
                'amor',
                [
                    'X_SELL' => [
                        'products' => ['apollon_A_false'],
                        'product_models' => ['amor'],
                        'groups' => ['groupA', 'groupB'],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ]
                ],
                [],
                new ReadValueCollection([
                    OptionValue::value('a_simple_select', 'optionB'),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    ScalarValue::value('a_yes_no', false),
                    ScalarValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ])
            ),
        ]);

        Assert::assertEquals($expectedProducts, $product);
    }

    /**
     * @group ce
     */
    public function test_get_product_from_the_PQB_by_filtering_on_values()
    {
        $query = $this->getQuery();
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create(['limit' => 10]);

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $product = $query->fromProductQueryBuilder(
            $pqb,
            (int) $userId,
            ['a_price', 'a_localized_and_scopable_text_area', 'attribute_code'],
            'tablet',
            ['fr_FR']
        );

        $productDataAppolonA = $this->get('database_connection')->executeQuery(
            'SELECT id, created, updated FROM pim_catalog_product WHERE identifier = "apollon_A_false"'
        )->fetch();
        $productDataAppolonB = $this->get('database_connection')->executeQuery(
            'SELECT id, created, updated FROM pim_catalog_product WHERE identifier = "apollon_B_false"'
        )->fetch();


        $expectedProducts = new ConnectorProductList(2, [
            new ConnectorProduct(
                (int) $productDataAppolonA['id'],
                'apollon_A_false',
                new \DateTimeImmutable($productDataAppolonA['created']),
                new \DateTimeImmutable($productDataAppolonA['updated']),
                true,
                'familyA',
                ['categoryA2', 'categoryB', 'categoryC'],
                [],
                'amor',
                [
                    'X_SELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => ['groupA'],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ]
                ],
                [],
                new ReadValueCollection([
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                ])
            ),
            new ConnectorProduct(
                (int) $productDataAppolonB['id'],
                'apollon_B_false',
                new \DateTimeImmutable($productDataAppolonB['created']),
                new \DateTimeImmutable($productDataAppolonB['updated']),
                true,
                'familyA',
                ['categoryA1', 'categoryA2'],
                ['groupA', 'groupB'],
                'amor',
                [
                    'X_SELL' => [
                        'products' => ['apollon_A_false'],
                        'product_models' => ['amor'],
                        'groups' => ['groupA', 'groupB'],
                    ],
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'PACK' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ],
                    'SUBSTITUTION' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => []
                    ]
                ],
                [],
                new ReadValueCollection([
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                ])
            ),
        ]);

        Assert::assertEquals($expectedProducts, $product);
    }

    /**
     * @group ce
     */
    public function test_get_product_from_an_identifier()
    {
        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $query = $this->getQuery();
        $product = $query->fromProductIdentifier('apollon_B_false', (int) $userId);

        $productData = $this->get('database_connection')->executeQuery(
            'SELECT id, created, updated FROM pim_catalog_product WHERE identifier = "apollon_B_false"'
        )->fetch();

        $expectedProduct = new ConnectorProduct(
            (int) $productData['id'],
            'apollon_B_false',
            new \DateTimeImmutable($productData['created']),
            new \DateTimeImmutable($productData['updated']),
            true,
            'familyA',
            ['categoryA1', 'categoryA2'],
            ['groupA', 'groupB'],
            'amor',
            [
                'X_SELL' => [
                    'products' => ['apollon_A_false'],
                    'product_models' => ['amor'],
                    'groups' => ['groupA', 'groupB'],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ]
            ],
            [],
            new ReadValueCollection([
                OptionValue::value('a_simple_select', 'optionB'),
                PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                ScalarValue::value('a_yes_no', false),
                ScalarValue::value('a_number_float', '12.5000'),
                ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
            ])
        );

        $this->assertEquals($expectedProduct, $product);
    }

    public function test_it_throws_an_exception_when_product_is_not_found()
    {
        $this->expectException(ObjectNotFoundException::class);
        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $query = $this->getQuery();
        $query->fromProductIdentifier('foo', (int) $userId);
    }

    public function test_it_returns_empty_associations_if_there_is_no_association_type()
    {
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_association_type_translation');
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_association_type');

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "admin"', [], 0);

        $query = $this->getQuery();
        $product = $query->fromProductIdentifier('apollon_B_false', (int)$userId);

        Assert::assertSame([], $product->associations());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createVariantProduct($identifier, array $data = []) : ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * Each time we create a product model, a batch job is pushed into the queue to calculate the
     * completeness of its descendants.
     *
     * @param array $data
     *
     * @return ProductModelInterface
     * @throws \Exception
     */
    private function createProductModel(array $data = []) : ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function getQuery(): GetConnectorProducts
    {
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_from_identifiers');
    }
}

