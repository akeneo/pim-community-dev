<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class SqlGetConnectorProductsWithPermissionsIntegration extends TestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    private PermissionFixturesLoader $loader;
    private Connection $connection;
    private int $maryUserId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->maryUserId = (int)$this->connection->executeQuery(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => 'mary']
        )->fetchOne();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    /**
     * @test
     */
    public function it_applies_permissions_on_empty_product_list(): void
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create([
            'limit' => 0,
        ]);

        Assert::assertEquals(
            new ConnectorProductList(0, []),
            $this->getQuery()->fromProductQueryBuilder($pqb, $this->maryUserId, null, null, null)
        );
    }

    /**
     * @test
     */
    public function it_gets_connector_products_by_applying_permissions_on_locale_and_attribute_in_values(): void
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $productData = $this->getProductData('variant_product');
        $expectedProduct = new ConnectorProduct(
            Uuid::fromString($productData['uuid']),
            'variant_product',
            new \DateTimeImmutable($productData['created'], new \DateTimeZone('UTC')),
            new \DateTimeImmutable($productData['updated'], new \DateTimeZone('UTC')),
            true,
            'family_permission',
            ['own_category'],
            [],
            'sub_product_model',
            [
                'X_SELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ],
            [],
            ['workflow_status' => 'working_copy'],
            new ReadValueCollection([
                ScalarValue::value('sku', 'variant_product'),
                ScalarValue::value('variant_product_axis_attribute', true),
                ScalarValue::localizableValue('variant_product_edit_attribute', true, 'en_US'),
                ScalarValue::localizableValue('variant_product_edit_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('variant_product_view_attribute', true, 'en_US'),
                ScalarValue::localizableValue('variant_product_view_attribute', true, 'fr_FR'),
                ScalarValue::value('sub_product_model_axis_attribute', true),
                ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'en_US'),
                ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'en_US'),
                ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'en_US'),
                ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('root_product_model_view_attribute', true, 'en_US'),
                ScalarValue::localizableValue('root_product_model_view_attribute', true, 'fr_FR'),
            ]),
            null,
            null
        );
        $expectedProductList = new ConnectorProductList(1, [$expectedProduct]);

        $query = $this->getQuery();
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create(
            ['limit' => 10]
        );

        Assert::assertEquals(
            $expectedProductList,
            $query->fromProductQueryBuilder($pqb, $this->maryUserId, null, null, null)
        );
        Assert::assertEquals(
            $expectedProductList,
            $query->fromProductUuids([Uuid::fromString($productData['uuid'])], $this->maryUserId, null, null, null)
        );
        Assert::assertEquals(
            $expectedProduct,
            $query->fromProductUuid(Uuid::fromString($productData['uuid']), $this->maryUserId)
        );
    }

    /**
     * @test
     */
    public function it_gets_connector_products_by_applying_permissions_on_associations(): void
    {
        $this->loader->loadProductsForAssociationPermissions();

        $productViewUuid = $this->getProductData('product_view')['uuid'];
        $expectedAssociations = [
            'X_SELL' => [
                'products' => [
                    ['identifier' => 'product_view', 'uuid' => $productViewUuid],
                ],
                'product_models' => [],
                'groups' => [],
            ],
            'UPSELL' => [
                'products' => [],
                'product_models' => [],
                'groups' => [],
            ],
            'PACK' => [
                'products' => [],
                'product_models' => [],
                'groups' => [],
            ],
            'SUBSTITUTION' => [
                'products' => [],
                'product_models' => [],
                'groups' => [],
            ],
        ];

        $variantProductUuid = $this->getProductData('variant_product')['uuid'];
        Assert::assertEquals(
            $expectedAssociations,
            $this->getQuery()->fromProductUuid(Uuid::fromString($variantProductUuid), $this->maryUserId)->associations()
        );
    }

    /**
     * @test
     */
    public function it_gets_connector_products_by_applying_permissions_on_quantified_associations(): void
    {
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->loader->loadProductsForQuantifiedAssociationPermissions();

        $expectedQuantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    [
                        'identifier' => 'product_viewable_by_everybody',
                        'quantity' => 2,
                        'uuid' => $this->getProductData('product_viewable_by_everybody')['uuid'],
                    ],
                    [
                        'identifier' => 'product_without_category',
                        'quantity' => 3,
                        'uuid' => $this->getProductData('product_without_category')['uuid'],
                    ],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                    ['identifier' => 'product_model_without_category', 'quantity' => 6],
                ],
            ],
        ];

        $variantProductUuid = $this->getProductData('product_associated_with_product_and_product_model')['uuid'];
        Assert::assertEquals(
            $expectedQuantifiedAssociations,
            $this->getQuery()->fromProductUuid(Uuid::fromString($variantProductUuid), $this->maryUserId)->quantifiedAssociations()
        );
    }

    /**
     * @test
     */
    public function it_gets_connector_products_by_applying_permissions_on_categories(): void
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $uuid = $this->getProductData('colored_sized_sweat_own')['uuid'];
        Assert::assertEquals(
            ['own_category'],
            $this->getQuery()->fromProductUuid(Uuid::fromString($uuid), $this->maryUserId)->categoryCodes()
        );
    }

    /**
     * @test
     */
    public function it_gets_connector_products_by_identifiers_and_applying_permissions_on_locale_and_attribute_in_values(): void
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $productData = $this->getProductData('variant_product');

        $expectedProduct = new ConnectorProduct(
            Uuid::fromString($productData['uuid']),
            'variant_product',
            new \DateTimeImmutable($productData['created'], new \DateTimeZone('UTC')),
            new \DateTimeImmutable($productData['updated'], new \DateTimeZone('UTC')),
            true,
            'family_permission',
            ['own_category'],
            [],
            'sub_product_model',
            [
                'X_SELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ],
            [],
            ['workflow_status' => 'working_copy'],
            new ReadValueCollection([
                ScalarValue::value('sku', 'variant_product'),
                ScalarValue::value('variant_product_axis_attribute', true),
                ScalarValue::localizableValue('variant_product_edit_attribute', true, 'en_US'),
                ScalarValue::localizableValue('variant_product_edit_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('variant_product_view_attribute', true, 'en_US'),
                ScalarValue::localizableValue('variant_product_view_attribute', true, 'fr_FR'),
                ScalarValue::value('sub_product_model_axis_attribute', true),
                ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'en_US'),
                ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'en_US'),
                ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'en_US'),
                ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'fr_FR'),
                ScalarValue::localizableValue('root_product_model_view_attribute', true, 'en_US'),
                ScalarValue::localizableValue('root_product_model_view_attribute', true, 'fr_FR'),
            ]),
            null,
            null
        );
        $expectedProductList = new ConnectorProductList(1, [$expectedProduct]);

        Assert::assertEquals(
            $expectedProductList,
            $this->getQuery()->fromProductUuids([Uuid::fromString($productData['uuid'])],
                $this->maryUserId,
                null,
                null,
                null)
        );
        Assert::assertEquals(
            $expectedProduct,
            $this->getQuery()->fromProductUuid(Uuid::fromString($productData['uuid']), $this->maryUserId)
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_product_is_not_viewable_by_user(): void
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $uuid = $this->getProductData('colored_sized_sweat_no_view')['uuid'];
        $this->expectException(ObjectNotFoundException::class);
        $this->expectExceptionMessage(\sprintf('Product "%s" is not viewable by user id "%s".', $uuid, $this->maryUserId));
        $this->getQuery()->fromProductUuid(Uuid::fromString($uuid), $this->maryUserId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    private function getQuery(): GetConnectorProducts
    {
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_from_uuids');
    }

    private function getProductData(string $identifier): array
    {
        return $this->connection->executeQuery(
            'SELECT BIN_TO_UUID(uuid) AS uuid, created, updated FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $identifier]
        )->fetchAssociative();
    }
}
