<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Connector\Writer\File\Flat\Uuid;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\Uuid\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
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
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class SqlGetConnectorProductsWithOptionsIntegration extends TestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    protected MessageBusInterface $messageBus;
    private ProductInterface $productA;
    private ProductInterface $productB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

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

        $this->productA = $this->createProductFromUserIntents(
            'apollon_A_false',
            [
                new SetCategories(['categoryB', 'categoryC']),
                new ChangeParent('amor'),
                new SetSimpleSelectValue('a_simple_select', null, null, 'optionA'),
                new SetBooleanValue('a_yes_no', null, null, false),
                new AssociateQuantifiedProductModels('PRODUCT_SET', [new QuantifiedEntity('amor', 4)])
            ]
        );

        $this->productB = $this->createProductFromUserIntents(
            'apollon_B_false',
            [
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
            ]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @group ce
     */
    public function test_get_product_from_a_uuid()
    {
        $userId = $this->getUserId('admin');

        $query = $this->getQuery();
        $product = $query->fromProductUuid($this->productB->getUuid(), (int) $userId);

        $productData = $this->get('database_connection')->executeQuery(
            'SELECT BIN_TO_UUID(uuid) AS uuid, created, updated FROM pim_catalog_product WHERE uuid = :product_uuid',
            ['product_uuid' => [$this->productB->getUuid()->getBytes()]],
            ['product_uuid' => Connection::PARAM_STR_ARRAY]
        )->fetch();

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
                    'products' => [$this->productA->getUuid()->toString()],
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
                    'products' => [['quantity' => 6, 'uuid' => $this->productA->getUuid()->toString()]],
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
                ScalarValue::value('a_yes_no', false),
                ScalarValue::value('a_number_float', '12.5000'),
                ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
            ]),
            null,
            null
        );

        $this->assertEquals($expectedProduct, $product);
    }

    /**
     * @TODOgroup ce
     */
    public function test_get_product_from_uuids()
    {
        $query = $this->getQuery();

        $userId = $this->getUserId('admin');

        $connectorProductList = $query->fromProductUuids([$this->productA->getUuid(), $this->productB->getUuid()], (int) $userId, null, null, null);

        $productDataAppolonA = $this->get('database_connection')->executeQuery(
            'SELECT BIN_TO_UUID(uuid) AS uuid, created, updated FROM pim_catalog_product WHERE uuid = :product_uuid',
            ['product_uuid' => [$this->productA->getUuid()->getBytes()]],
            ['product_uuid' => Connection::PARAM_STR_ARRAY]
        )->fetch();
        $productDataAppolonB = $this->get('database_connection')->executeQuery(
            'SELECT BIN_TO_UUID(uuid) AS uuid, created, updated FROM pim_catalog_product WHERE uuid = :product_uuid',
            ['product_uuid' => [$this->productB->getUuid()->getBytes()]],
            ['product_uuid' => Connection::PARAM_STR_ARRAY]
        )->fetch();


        $expectedProducts = new ConnectorProductList(2, [
            new ConnectorProduct(
                Uuid::fromString($productDataAppolonA['uuid']),
                'apollon_A_false',
                new \DateTimeImmutable($productDataAppolonA['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataAppolonA['updated'], new \DateTimeZone('UTC')),
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
                    ScalarValue::value('a_yes_no', false),
                    ScalarValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ]),
                null,
                null
            ),
            new ConnectorProduct(
                Uuid::fromString($productDataAppolonB['uuid']),
                'apollon_B_false',
                new \DateTimeImmutable($productDataAppolonB['created'], new \DateTimeZone('UTC')),
                new \DateTimeImmutable($productDataAppolonB['updated'], new \DateTimeZone('UTC')),
                true,
                'familyA',
                ['categoryA1', 'categoryA2'],
                ['groupA', 'groupB'],
                'amor',
                [
                    'X_SELL' => [
                        'products' => [$this->productA->getUuid()->toString()],
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
                        'products' => [['quantity' => 6, 'uuid' => $this->productA->getUuid()->toString()]],
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
                    ScalarValue::value('a_yes_no', false),
                    ScalarValue::value('a_number_float', '12.5000'),
                    ScalarValue::scopableLocalizableValue('a_localized_and_scopable_text_area', 'my pink tshirt', 'ecommerce', 'en_US'),
                ]),
                null,
                null
            ),
        ]);

        Assert::assertEquals($expectedProducts, $connectorProductList);
    }

    public function test_it_throws_an_exception_when_product_is_not_found()
    {
        $this->expectException(ObjectNotFoundException::class);
        $userId = $this->getUserId('admin');

        $query = $this->getQuery();
        $query->fromProductUuid(Uuid::fromString('54c44059-8683-4d07-9137-aba55b2e7440'), (int) $userId);
    }

    public function test_it_returns_empty_associations_if_there_is_no_association_type()
    {
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_association_type_translation');
        $this->get('database_connection')->executeQuery('DELETE FROM pim_catalog_association_type');

        $userId = $this->getUserId('admin');

        $query = $this->getQuery();
        $product = $query->fromProductUuid($this->productB->getUuid(), (int)$userId);

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
        $userId = $this->getUserId('admin');

        $product = $this->createProductFromUserIntents(
            $identifier,
            [],
            $userId
        );

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
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_from_uuids_with_options');
    }
}
