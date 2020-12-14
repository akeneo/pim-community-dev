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
use PHPUnit\Framework\Assert;

class SqlGetConnectorProductsWithPermissionsIntegration extends TestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /** @var PermissionFixturesLoader */
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    /**
     * @test
     */
    public function it_applies_permissions_on_empty_product_list()
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create([
            'limit' => 0
        ]);

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productList = $this->getQuery()->fromProductQueryBuilder($pqb, (int) $userId, null, null, null);

        $expectedProductList = new ConnectorProductList(0, []);

        Assert::assertEquals($expectedProductList, $productList);
    }

    /**
     * @test
     */
    public function it_get_connector_products_by_applying_permissions_on_locale_and_attribute_in_values()
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();
        $query = $this->getQuery();
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create(['limit' => 10]);

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productList = $query->fromProductQueryBuilder($pqb, (int) $userId, null, null, null);
        $product = $query->fromProductIdentifier('variant_product', (int) $userId);

        $productData = $this->get('database_connection')->executeQuery(
            'SELECT id, created, updated FROM pim_catalog_product WHERE identifier = "variant_product"'
        )->fetch();

        $expectedProduct = new ConnectorProduct(
            (int) $productData['id'],
            'variant_product',
            new \DateTimeImmutable($productData['created']),
            new \DateTimeImmutable($productData['updated']),
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
            ['workflow_status' => 'working_copy'],
            new ReadValueCollection([
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
            null
        );
        $expectedProductList = new ConnectorProductList(1, [$expectedProduct]);

        Assert::assertEquals($expectedProductList, $productList);
        Assert::assertEquals($expectedProduct, $product);

    }


    /**
     * @test
     */
    public function it_get_connector_products_by_applying_permissions_on_associations()
    {
        $this->loader->loadProductsForAssociationPermissions();
        $query = $this->getQuery();

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $product = $query->fromProductIdentifier('variant_product', (int) $userId);

        Assert::assertEquals([
            'X_SELL' => [
                'products' => ['product_view'],
                'product_models' => [],
                'groups' => [],
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
        ], $product->associations());
    }

    /**
     * @test
     */
    public function it_get_connector_products_by_applying_permissions_on_quantified_associations()
    {
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->loader->loadProductsForQuantifiedAssociationPermissions();
        $query = $this->getQuery();

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $product = $query->fromProductIdentifier('product_associated_with_product_and_product_model', (int) $userId);

        Assert::assertEquals([
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'product_viewable_by_everybody', 'quantity' => 2],
                    ['identifier' => 'product_without_category', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'product_model_viewable_by_everybody', 'quantity' => 5],
                    ['identifier' => 'product_model_without_category', 'quantity' => 6],
                ],
            ],
        ], $product->quantifiedAssociations());
    }

    /**
     * @test
     */
    public function it_get_connector_products_by_applying_permissions_on_categories()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();
        $query = $this->getQuery();

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $product = $query->fromProductIdentifier('colored_sized_sweat_own', (int) $userId);

        Assert::assertEquals(['own_category'], $product->categoryCodes());
    }


    /**
     * @test
     */
    public function it_throws_an_exception_if_product_is_not_viewable_by_user()
    {
        $this->expectException(ObjectNotFoundException::class);
        $this->loader->loadProductModelsFixturesForCategoryPermissions();
        $query = $this->getQuery();

        $userId = $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $query->fromProductIdentifier('colored_sized_sweat_no_view', (int) $userId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): GetConnectorProducts
    {
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_from_identifiers');
    }
}
