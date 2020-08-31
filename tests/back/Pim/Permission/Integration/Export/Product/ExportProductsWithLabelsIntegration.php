<?php

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Export\Product;

use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader as AssetManagerFixturesLoader;
use Akeneo\ReferenceEntity\Common\Helper\FixturesLoader as ReferenceEntityFixturesLoader;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase as ExportTestCase;

class ExportProductsWithLabelsIntegration extends ExportTestCase
{
    public function testProductExportWithLabels()
    {
        $expectedCsvWithTranslations = <<<CSV
Activé;Catégories;"Collection images";Famille;Groupes;"Les couleurs";"Les designers";[sku]
Oui;;Nike,Addidas;[clothing];;"Philippe Starck,Marc Jacobs";"Philippe Starck";a_product

CSV;
        $this->assertProductExport(
            $expectedCsvWithTranslations,
            ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'fr_FR']
        );
    }

    public function testProductExportWithMissingLabelsForTheLocale()
    {
        $expectedCsvWithNoTranslations = <<<CSV
[assets];[categories];[creator];[designer_influence];[enabled];[family];[groups];[sku]
[nike],[addidas];;[starck];[starck],[jacobs];[yes];[clothing];;a_product

CSV;
        $this->assertProductExport(
            $expectedCsvWithNoTranslations,
            ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'unknown_locale']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures(): void
    {
        // Reference entities
        /** @var ReferenceEntityFixturesLoader $referenceEntityFixturesLoader */
        $referenceEntityFixturesLoader = $this->get('akeneoreference_entity.tests.helper.fixtures_loader');
        $referenceEntityFixturesLoader->referenceEntity('designer')->load();
        $referenceEntityFixturesLoader->record('designer', 'starck')
            ->withValues(['label' => [['channel' => null, 'locale' => 'fr_FR', 'data' => 'Philippe Starck',]]])
            ->load();
        $referenceEntityFixturesLoader->record('designer', 'jacobs')
            ->withValues(['label' => [['channel' => null, 'locale' => 'fr_FR', 'data' => 'Marc Jacobs']]])
            ->load();
        $this->createAttribute(
            [
                'code' => 'creator',
                'type' => 'akeneo_reference_entity',
                'group' => 'attributeGroupA',
                'localizable' => false,
                'scopable' => false,
                'labels' => [
                    'fr_FR' => 'Les designers',
                ],
                'reference_data_name' => 'designer'
            ]
        );
        $this->createAttribute(
            [
                'code' => 'designer_influence',
                'type' => 'akeneo_reference_entity_collection',
                'group' => 'attributeGroupA',
                'localizable' => false,
                'scopable' => false,
                'labels' => [
                    'fr_FR' => 'Les couleurs',
                ],
                'reference_data_name' => 'designer'
            ]
        );


        // Asset collection
        /** @var AssetManagerFixturesLoader $assetManagerFixturesLoader */
        $assetManagerFixturesLoader = $this->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $assetManagerFixturesLoader->assetFamily('brand')->load();
        $assetManagerFixturesLoader->asset('brand', 'nike')
            ->withValues(['label' => [['channel' => null, 'locale' => 'fr_FR', 'data' => 'Nike']]])
            ->load();
        $assetManagerFixturesLoader->asset('brand', 'addidas')
            ->withValues(['label' => [['channel' => null, 'locale' => 'fr_FR', 'data' => 'Addidas']]])
            ->load();

        $this->createAttribute(
            [
                'code' => 'assets',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'attributeGroupA',
                'localizable' => false,
                'scopable' => false,
                'labels' => [
                    'fr_FR' => 'Collection images',
                ],
                'reference_data_name' => 'brand'
            ]
        );

        // Families
        $this->createFamily(
            [
                'code' => 'clothing',
                'attributes' => ['sku', 'creator', 'designer_influence', 'assets'],
                'labels' => [],
            ]
        );

        // Products
        $this->createVariantProduct(
            'a_product',
            [
                'family' => 'clothing',
                'categories' => [],
                'values' => [
                    'creator' => [['data' => 'starck', 'locale' => null, 'scope' => null]],
                    'designer_influence' => [['data' => ['starck', 'jacobs'], 'locale' => null, 'scope' => null]],
                    'assets' => [['data' => ['nike', 'addidas'], 'locale' => null, 'scope' => null]],
                ],
            ]
        );
    }
}
