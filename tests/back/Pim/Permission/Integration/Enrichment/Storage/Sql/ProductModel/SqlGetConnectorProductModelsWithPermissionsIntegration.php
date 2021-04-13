<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SqlGetConnectorProductModelsWithPermissionsIntegration extends TestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /** @var PermissionFixturesLoader */
    private $loader;

    /**
     * @test
     */
    public function it_applies_permissions_on_empty_product_model_list()
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_search_after_size_factory_external_api')->create([
            'limit' => 0
        ]);

        $productModelList = $this->getQuery()->fromProductQueryBuilder(
            $pqb,
            (int) $this->getRedactorUserId(),
            null,
            null,
            null
        );

        $expectedProductModelList = new ConnectorProductModelList(0, []);

        Assert::assertEquals($expectedProductModelList, $productModelList);
    }

    /**
     * @test
     */
    public function it_applies_permissions_on_attributes_and_locales_while_retrieving_connector_product_models(): void
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $pqb = $this->get('pim_catalog.query.product_model_query_builder_search_after_size_factory_external_api')
                    ->create(['limit' => 10]);
        $actualProductModelList = $this->getQuery()->fromProductQueryBuilder(
            $pqb,
            $this->getRedactorUserId(),
            null,
            null,
            null
        );

        $dataRootPm = $this->getIdAndDatesFromProductModelCode('root_product_model');
        $dataSubPm = $this->getIdAndDatesFromProductModelCode('sub_product_model');

        $emptyAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [],
                'groups' => []
            ],
            'SUBSTITUTION' => [
                'products' => [],
                'product_models' => [],
                'groups' => []
            ],
            'UPSELL' => [
                'products' => [],
                'product_models' => [],
                'groups' => []
            ],
            'X_SELL' => [
                'products' => [],
                'product_models' => [],
                'groups' => [],
            ],
        ];

        $expectedProductModelList = new ConnectorProductModelList(2, [
            new ConnectorProductModel(
                (int)$dataRootPm['id'],
                'root_product_model',
                new \DateTimeImmutable($dataRootPm['created']),
                new \DateTimeImmutable($dataRootPm['updated']),
                null,
                'family_permission',
                'family_variant_permission',
                ['workflow_status' => 'working_copy'],
                $emptyAssociations,
                [],
                ['own_category'],
                new ReadValueCollection([
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'fr_FR'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'fr_FR'),
                ])
            ),
            new ConnectorProductModel(
                (int)$dataSubPm['id'],
                'sub_product_model',
                new \DateTimeImmutable($dataSubPm['created']),
                new \DateTimeImmutable($dataSubPm['updated']),
                'root_product_model',
                'family_permission',
                'family_variant_permission',
                ['workflow_status' => 'working_copy'],
                $emptyAssociations,
                [],
                ['own_category'],
                new ReadValueCollection([
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'fr_FR'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'fr_FR'),
                    ScalarValue::value('sub_product_model_axis_attribute', true),
                    ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'fr_FR'),
                    ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'fr_FR'),
                ])
            ),
        ]);

        Assert::assertEquals($expectedProductModelList, $actualProductModelList);
    }

    /**
     * @test
     */
    function it_applies_permissions_on_associations_while_retrieving_connector_product_models(): void
    {
        $this->loader->loadProductsForAssociationPermissions();
        $this->updateProductModelAssociations('root_product_model',
            [
                'X_SELL' => [
                    'products' => ['product_no_view', 'product_own'],
                    'product_models' => ['product_model_view'],
                ]
            ]
        );
        $this->updateProductModelAssociations('sub_product_model',
            [
                'X_SELL' => [
                    'products' => ['product_view'],
                    'product_models' => ['product_model_no_view'],
                ]
            ]
        );

        $connectorRootProductModel = $this->getQuery()->fromProductModelCode(
            'root_product_model',
            $this->getRedactorUserId(),
            null,
            null,
            null
        );

        Assert::assertSame(
            [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'X_SELL' => [
                    'products' => ['product_own'],
                    'product_models' => ['product_model_view'],
                    'groups' => [],
                ],
            ],
            $connectorRootProductModel->associations()
        );

        $connectorSubProductModel = $this->getQuery()->fromProductModelCode(
            'sub_product_model',
            $this->getRedactorUserId(),
            null,
            null,
            null
        );

        Assert::assertSame(
            [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => []
                ],
                'X_SELL' => [
                    'products' => ['product_own', 'product_view'],
                    'product_models' => ['product_model_view'],
                    'groups' => [],
                ],
            ],
            $connectorSubProductModel->associations()
        );
    }

    /**
     * @test
     */
    public function it_applies_permissions_on_attributes_and_locales_while_retrieving_connector_product_models_by_codes(): void
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $actualProductModelList = $this->getQuery()->fromProductModelCodes(
            ['root_product_model', 'sub_product_model'],
            $this->getRedactorUserId(),
            null,
            null,
            null
        );

        $dataRootPm = $this->getIdAndDatesFromProductModelCode('root_product_model');
        $dataSubPm = $this->getIdAndDatesFromProductModelCode('sub_product_model');

        $emptyAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [],
                'groups' => []
            ],
            'SUBSTITUTION' => [
                'products' => [],
                'product_models' => [],
                'groups' => []
            ],
            'UPSELL' => [
                'products' => [],
                'product_models' => [],
                'groups' => []
            ],
            'X_SELL' => [
                'products' => [],
                'product_models' => [],
                'groups' => [],
            ],
        ];

        $expectedProductModelList = new ConnectorProductModelList(2, [
            new ConnectorProductModel(
                (int)$dataRootPm['id'],
                'root_product_model',
                new \DateTimeImmutable($dataRootPm['created']),
                new \DateTimeImmutable($dataRootPm['updated']),
                null,
                'family_permission',
                'family_variant_permission',
                ['workflow_status' => 'working_copy'],
                $emptyAssociations,
                [],
                ['own_category'],
                new ReadValueCollection([
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'fr_FR'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'fr_FR'),
                ])
            ),
            new ConnectorProductModel(
                (int)$dataSubPm['id'],
                'sub_product_model',
                new \DateTimeImmutable($dataSubPm['created']),
                new \DateTimeImmutable($dataSubPm['updated']),
                'root_product_model',
                'family_permission',
                'family_variant_permission',
                ['workflow_status' => 'working_copy'],
                $emptyAssociations,
                [],
                ['own_category'],
                new ReadValueCollection([
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_edit_attribute', true, 'fr_FR'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('root_product_model_view_attribute', true, 'fr_FR'),
                    ScalarValue::value('sub_product_model_axis_attribute', true),
                    ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('sub_product_model_edit_attribute', true, 'fr_FR'),
                    ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'en_US'),
                    ScalarValue::localizableValue('sub_product_model_view_attribute', true, 'fr_FR'),
                ])
            ),
        ]);

        Assert::assertEquals($expectedProductModelList, $actualProductModelList);
    }

    /**
     * @test
     */
    public function it_get_connector_product_models_by_applying_permissions_on_quantified_associations()
    {
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->loader->loadProductsForQuantifiedAssociationPermissions();

        $productModel = $this->getQuery()->fromProductModelCode(
            'product_model_associated_with_product_and_product_model',
            $this->getRedactorUserId()
        );

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
        ], $productModel->quantifiedAssociations());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getRedactorUserId(): int
    {
        $redactorId = $this->get('database_connection')
                           ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        return (int)$redactorId;
    }

    private function getQuery(): GetConnectorProductModels
    {
        return $this->get('akeneo.pim.enrichment.product.connector.get_product_models_from_codes');
    }

    private function getIdAndDatesFromProductModelCode(string $productModelCode): array
    {
        return $this->get('database_connection')->fetchAssoc(
            'select id, created, updated from pim_catalog_product_model where code = :productModelCode',
            [
                'productModelCode' => $productModelCode,
            ]
        );
    }

    private function updateProductModelAssociations(string $productModelCode, array $associations): void
    {
        $this->updateProductModel($productModelCode, [
            'associations' => $associations,
        ]);
    }

    private function updateProductModelQuantifiedAssociations(string $productModelCode, array $quantifiedAssociations): void
    {
        $this->updateProductModel($productModelCode, [
            'quantified_associations' => $quantifiedAssociations,
        ]);
    }

    private function updateProductModel(string $productModelCode, array $data): void
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByidentifier($productModelCode);
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

}
