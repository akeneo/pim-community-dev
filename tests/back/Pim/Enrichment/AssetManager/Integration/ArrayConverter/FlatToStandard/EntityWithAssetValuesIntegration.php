<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\AssetManager\Integration\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class EntityWithAssetValuesIntegration extends TestCase
{
    private ArrayConverterInterface $productConverter;
    private ArrayConverterInterface $productModelConverter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productConverter = $this->get('akeneo_assetmanager.job.array_converter.flat_to_standard.product');
        $this->productModelConverter = $this->get('akeneo_assetmanager.job.array_converter.flat_to_standard.product_model');

        $assetAttribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($assetAttribute, [
            'code' => 'asset_attr',
            'type' => AssetCollectionType::ASSET_COLLECTION,
            'group' => 'attributeGroupB',
        ]);
        $this->get('pim_catalog.saver.attribute')->save($assetAttribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     *
     * More complex tests are done in the specification. This test is here to
     * check the service is well decorated.
     */
    public function it_removes_asset_columns_when_converts_an_item_in_product_converter(): void
    {
        $item = [
            'sku' => 'test',
            'a_text' => 'this is a text',
            'asset_attr' => 'code1,code2',
            'asset_attr-file_path' => 'path1,path2',
        ];

        $result = $this->productConverter->convert($item, []);
        self::assertArrayHasKey('values', $result);
        self::assertArrayHasKey('sku', $result['values']);
        self::assertArrayHasKey('a_text', $result['values']);
        self::assertArrayHasKey('asset_attr', $result['values']);
        self::assertArrayNotHasKey('asset_attr-file_path', $result['values']);
    }

    /**
     * @test
     *
     * More complex tests are done in the specification. This test is here to
     * check the service is well decorated.
     */
    public function it_removes_asset_columns_when_converts_an_item_in_product_model_converter(): void
    {
        $item = [
            'code' => 'test',
            'a_text' => 'this is a text',
            'asset_attr' => 'code1,code2',
            'asset_attr-file_path' => 'path1,path2',
        ];

        $result = $this->productModelConverter->convert($item, []);
        self::assertArrayHasKey('values', $result);
        self::assertArrayHasKey('a_text', $result['values']);
        self::assertArrayHasKey('asset_attr', $result['values']);
        self::assertArrayNotHasKey('asset_attr-file_path', $result['values']);
    }
}
