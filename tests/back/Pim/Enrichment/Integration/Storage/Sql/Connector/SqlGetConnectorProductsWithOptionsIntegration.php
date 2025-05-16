<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\NumberValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class SqlGetConnectorProductsWithOptionsIntegration extends TestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    private int $adminUserId;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->adminUserId = $this->getUserId('admin');

        $this->createQuantifiedAssociationType('PRODUCT_SET');
        $this->createQuantifiedAssociationType('ANOTHER_PRODUCT_SET');

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

        $this->createProduct('apollon_A_false', [
            new SetCategories(['categoryB', 'categoryC']),
            new ChangeParent('amor'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
            new SetBooleanValue('a_yes_no', null, null, false),
            new AssociateQuantifiedProductModels('PRODUCT_SET', [new QuantifiedEntity('amor', 4)]),
        ]);

        $this->createProduct('apollon_B_false', [
            new SetCategories(['categoryA1']),
            new ChangeParent('amor'),
            new SetGroups(['groupA', 'groupB']),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
            new SetBooleanValue('a_yes_no', null, null, false),
            new AssociateProducts('X_SELL', ['apollon_A_false']),
            new AssociateProductModels('X_SELL', ['amor']),
            new AssociateGroups('X_SELL', ['groupB']),
            new AssociateQuantifiedProducts('PRODUCT_SET', [new QuantifiedEntity('apollon_A_false', 6)]),
            new AssociateQuantifiedProductModels('ANOTHER_PRODUCT_SET', [new QuantifiedEntity('amor', 2)]),
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @group ce
     */
    public function test_get_product_from_the_PQB()
    {
        $productDataApollonA = $this->getProductData('apollon_A_false');
        $productDataApollonB = $this->getProductData('apollon_B_false');
        $expectedProducts = new ConnectorProductList(2, [
            new ConnectorProduct(
                Uuid::fromString($productDataApollonA['uuid']),
                'apollon_A_false',
                new \DateTimeImmutable($productDataApollonA['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataApollonA['updated'], new \DateTimeZone('UTC')),
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
                [
                    'PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [['identifier' => 'amor', 'quantity' => 4]],
                    ],
                ],
                [],
                new ReadValueCollection([
                    new OptionValueWithLinkedData('a_simple_select','optionA', null, null, ['attribute' => 'a_simple_select', 'code' => 'optionA', 'labels' => ['en_US' => 'Option A',],]),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    IdentifierValue::value('sku', true, 'apollon_A_false'),
                    ScalarValue::value('a_yes_no', false),
                    NumberValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ]),
                null,
                null
            ),
            new ConnectorProduct(
                Uuid::fromString($productDataApollonB['uuid']),
                'apollon_B_false',
                new \DateTimeImmutable($productDataApollonB['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataApollonB['updated'], new \DateTimeZone('UTC')),
                true,
                'familyA',
                ['categoryA1', 'categoryA2'],
                ['groupA', 'groupB'],
                'amor',
                [
                    'X_SELL' => [
                        'products' => [
                            ['identifier' => 'apollon_A_false', 'uuid' => $productDataApollonA['uuid']],
                        ],
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
                [
                    'PRODUCT_SET' => [
                        'products' => [['identifier' => 'apollon_A_false', 'quantity' => 6, 'uuid' => $productDataApollonA['uuid']]],
                        'product_models' => [],
                    ],
                    'ANOTHER_PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [['identifier' => 'amor', 'quantity' => 2]],
                    ],
                ],
                [],
                new ReadValueCollection([
                    new OptionValueWithLinkedData('a_simple_select','optionB', null, null, ['attribute' => 'a_simple_select', 'code' => 'optionB', 'labels' => ['en_US' => 'Option B',],]),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    IdentifierValue::value('sku', true, 'apollon_B_false'),
                    ScalarValue::value('a_yes_no', false),
                    NumberValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ]),
                null,
                null
            ),
        ]);

        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create(['limit' => 10]);
        $product = $this->getQuery()->fromProductQueryBuilder($pqb, $this->adminUserId, null, null, null);

        Assert::assertEquals($expectedProducts, $product);
    }

    /**
     * @group ce
     */
    public function test_get_product_from_the_PQB_by_filtering_on_values()
    {
        $productDataApollonA = $this->getProductData('apollon_A_false');
        $productDataApollonB = $this->getProductData('apollon_B_false');

        $expectedProducts = new ConnectorProductList(2, [
            new ConnectorProduct(
                Uuid::fromString($productDataApollonA['uuid']),
                'apollon_A_false',
                new \DateTimeImmutable($productDataApollonA['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataApollonA['updated'], new \DateTimeZone('UTC')),
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
                [
                    'PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [['identifier' => 'amor', 'quantity' => 4]],
                    ],
                ],
                [],
                new ReadValueCollection([
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                ]),
                null,
                null
            ),
            new ConnectorProduct(
                Uuid::fromString($productDataApollonB['uuid']),
                'apollon_B_false',
                new \DateTimeImmutable($productDataApollonB['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataApollonB['updated'], new \DateTimeZone('UTC')),
                true,
                'familyA',
                ['categoryA1', 'categoryA2'],
                ['groupA', 'groupB'],
                'amor',
                [
                    'X_SELL' => [
                        'products' => [
                            ['identifier' => 'apollon_A_false', 'uuid' => $productDataApollonA['uuid']],
                        ],
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
                [
                    'PRODUCT_SET' => [
                        'products' => [['identifier' => 'apollon_A_false', 'quantity' => 6, 'uuid' => $productDataApollonA['uuid']]],
                        'product_models' => [],
                    ],
                    'ANOTHER_PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [['identifier' => 'amor', 'quantity' => 2]],
                    ],
                ],
                [],
                new ReadValueCollection([
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                ]),
                null,
                null
            ),
        ]);

        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create(['limit' => 10]);
        $product = $this->getQuery()->fromProductQueryBuilder(
            $pqb,
            $this->adminUserId,
            ['a_price', 'a_localized_and_scopable_text_area', 'attribute_code'],
            'tablet',
            ['fr_FR']
        );
        Assert::assertEquals($expectedProducts, $product);
    }

    /**
     * @group ce
     */
    public function test_get_product_from_an_identifier_or_a_uuid()
    {
        $productData = $this->getProductData('apollon_B_false');
        $uuidApollonA = $this->getProductData('apollon_A_false')['uuid'];

        $expectedProduct = new ConnectorProduct(
            Uuid::fromString($productData['uuid']),
            'apollon_B_false',
            new \DateTimeImmutable($productData['created'], new \DateTimeZone('UTC')),
            new \DateTimeImmutable($productData['updated'], new \DateTimeZone('UTC')),
            true,
            'familyA',
            ['categoryA1', 'categoryA2'],
            ['groupA', 'groupB'],
            'amor',
            [
                'X_SELL' => [
                    'products' => [
                        ['identifier' => 'apollon_A_false', 'uuid' => $uuidApollonA],
                    ],
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
            [
                'PRODUCT_SET' => [
                    'products' => [['identifier' => 'apollon_A_false', 'quantity' => 6, 'uuid' => $uuidApollonA]],
                    'product_models' => [],
                ],
                'ANOTHER_PRODUCT_SET' => [
                    'products' => [],
                    'product_models' => [['identifier' => 'amor', 'quantity' => 2]],
                ],
            ],
            [],
            new ReadValueCollection([
                new OptionValueWithLinkedData('a_simple_select','optionB', null, null, ['attribute' => 'a_simple_select', 'code' => 'optionB', 'labels' => ['en_US' => 'Option B',],]),
                PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                IdentifierValue::value('sku', true, 'apollon_B_false'),
                ScalarValue::value('a_yes_no', false),
                NumberValue::value('a_number_float', '12.5000'),
                ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
            ]),
            null,
            null
        );

        $this->assertEquals(
            $expectedProduct,
            $this->getQuery()->fromProductUuid(Uuid::fromString($productData['uuid']), $this->adminUserId)
        );
    }

    /**
     * @group ce
     */
    public function test_get_product_from_identifiers_or_uuids()
    {
        $productDataApollonA = $this->getProductData('apollon_A_false');
        $productDataApollonB = $this->getProductData('apollon_B_false');

        $expectedProducts = new ConnectorProductList(2, [
            new ConnectorProduct(
                Uuid::fromString($productDataApollonA['uuid']),
                'apollon_A_false',
                new \DateTimeImmutable($productDataApollonA['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataApollonA['updated'], new \DateTimeZone('UTC')),
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
                [
                    'PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [['identifier' => 'amor', 'quantity' => 4]],
                    ],
                ],
                [],
                new ReadValueCollection([
                    new OptionValueWithLinkedData('a_simple_select','optionA', null, null, ['attribute' => 'a_simple_select', 'code' => 'optionA', 'labels' => ['en_US' => 'Option A',],]),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    IdentifierValue::value('sku', true, 'apollon_A_false'),
                    ScalarValue::value('a_yes_no', false),
                    NumberValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ]),
                null,
                null
            ),
            new ConnectorProduct(
                Uuid::fromString($productDataApollonB['uuid']),
                'apollon_B_false',
                new \DateTimeImmutable($productDataApollonB['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataApollonB['updated'], new \DateTimeZone('UTC')),
                true,
                'familyA',
                ['categoryA1', 'categoryA2'],
                ['groupA', 'groupB'],
                'amor',
                [
                    'X_SELL' => [
                        'products' => [
                            ['identifier' => 'apollon_A_false', 'uuid' => $productDataApollonA['uuid']],
                        ],
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
                [
                    'PRODUCT_SET' => [
                        'products' => [['identifier' => 'apollon_A_false', 'quantity' => 6, 'uuid' => $productDataApollonA['uuid']]],
                        'product_models' => [],
                    ],
                    'ANOTHER_PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [['identifier' => 'amor', 'quantity' => 2]],
                    ],
                ],
                [],
                new ReadValueCollection([
                    new OptionValueWithLinkedData('a_simple_select','optionB', null, null, ['attribute' => 'a_simple_select', 'code' => 'optionB', 'labels' => ['en_US' => 'Option B',],]),
                    PriceCollectionValue::value('a_price', new PriceCollection([new ProductPrice(50, 'EUR')])),
                    IdentifierValue::value('sku', true, 'apollon_B_false'),
                    ScalarValue::value('a_yes_no', false),
                    NumberValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ]),
                null,
                null
            ),
        ]);

        Assert::assertEquals($expectedProducts, $this->getQuery()->fromProductUuids(
            [Uuid::fromString($productDataApollonA['uuid']), Uuid::fromString($productDataApollonB['uuid'])],
            $this->adminUserId, null, null, null)
        );
    }

    public function test_it_throws_an_exception_when_product_is_not_found()
    {
        $this->expectException(ObjectNotFoundException::class);

        $uuid = Uuid::uuid4();
        $this->expectException(ObjectNotFoundException::class);
        $this->getQuery()->fromProductUuid($uuid, $this->adminUserId);
    }

    public function test_it_returns_empty_associations_if_there_is_no_association_type()
    {
        $this->connection->executeStatement('DELETE FROM pim_catalog_association_type_translation');
        $this->connection->executeStatement('DELETE FROM pim_catalog_association_type');

        $query = $this->getQuery();

        $productDataApollonB = $this->getProductData('apollon_B_false');
        $product = $query->fromProductUuid(Uuid::fromString($productDataApollonB['uuid']), $this->adminUserId);

        Assert::assertSame([], $product->associations());
    }

    public function test_it_filters_empty_option_labels(): void
    {
        // INSERT a NULL label translation for optionA
        $this->get('database_connection')->executeStatement(<<<SQL
            REPLACE INTO pim_catalog_attribute_option_value(option_id, locale_code, value)
                SELECT opt.id, 'fr_FR', NULL FROM pim_catalog_attribute_option opt
                INNER JOIN pim_catalog_attribute attr ON opt.attribute_id = attr.id
                WHERE attr.code = :attrCode and opt.code = :optCode
            SQL,
            [
                'attrCode' => 'a_simple_select',
                'optCode' => 'optionA',
            ]
        );

        $productDataApollonA = $this->getProductData('apollon_A_false');
        $result = $this->getQuery()->fromProductUuid(Uuid::fromString($productDataApollonA['uuid']), $this->adminUserId);

        Assert::assertInstanceOf(ConnectorProduct::class, $result);
        $simpleSelectValue = $result->values()->filter(
            static fn (ValueInterface $value): bool => 'a_simple_select' === $value->getAttributeCode()
        )->first();

        Assert::assertInstanceOf(OptionValueWithLinkedData::class, $simpleSelectValue);;
        Assert::assertSame(
            [
                'attribute' => 'a_simple_select',
                'code' => 'optionA',
                'labels' => ['en_US' => 'Option A'],
            ],
            $simpleSelectValue->getLinkedData()
        );
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
     * @param UserIntent[] $userIntents
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createProduct(string $identifier, array $userIntents = []) : ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createFromCollection(
            $this->adminUserId,
            $identifier,
            $userIntents
        ));

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_from_uuids_with_options');
    }

    private function getProductData(string $identifier): array
    {
        return $this->connection->executeQuery(<<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(uuid) AS uuid, created, updated
FROM pim_catalog_product
INNER JOIN pim_catalog_product_unique_data pcpud
    ON pcpud.product_uuid = pim_catalog_product.uuid
    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE raw_data = :identifier
SQL,
            ['identifier' => $identifier]
        )->fetchAssociative();
    }
}
