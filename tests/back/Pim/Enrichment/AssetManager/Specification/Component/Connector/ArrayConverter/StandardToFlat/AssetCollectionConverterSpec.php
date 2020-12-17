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

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\StandardToFlat\AssetCollectionConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCollectionConverterSpec extends ObjectBehavior
{
    function let(AttributeColumnsResolver $columnsResolver)
    {
        $this->beConstructedWith($columnsResolver);
    }

    function it_can_be_instantiated()
    {
        $this->beAnInstanceOf(AssetCollectionConverter::class);
    }

    function it_supports_asset_collection_attribute()
    {
        $assetCollectionAttribute = new Attribute();
        $assetCollectionAttribute->setType(AssetCollectionType::ASSET_COLLECTION);
        $this->supportsAttribute($assetCollectionAttribute)->shouldBe(true);

        $textAttribute = new Attribute();
        $assetCollectionAttribute->setType(AttributeTypes::TEXT);
        $this->supportsAttribute($textAttribute)->shouldBe(false);
    }

    function it_converts_an_asset_collection_value_from_standard_to_flat_format(
        AttributeColumnsResolver $columnsResolver
    ) {
        $values = [
            [
                'locale' => null,
                'scope' => null,
                'data' => ['asset1', 'asset2'],
            ],
        ];
        $columnsResolver->resolveFlatAttributeName('code', null, null)->willReturn('key1');

        $this->convert('code', $values)->shouldBe([
            'key1' => 'asset1,asset2',
        ]);
    }

    function it_converts_asset_collection_values_from_standard_to_flat_format(
        AttributeColumnsResolver $columnsResolver
    ) {
        $values = [
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
                'data' => ['asset1', 'asset2'],
            ],
            [
                'locale' => 'fr_FR',
                'scope' => 'mobile',
                'data' => ['asset3'],
            ],
        ];
        $columnsResolver->resolveFlatAttributeName('code', 'en_US', 'ecommerce')->willReturn('key1');
        $columnsResolver->resolveFlatAttributeName('code', 'fr_FR', 'mobile')->willReturn('key2');

        $this->convert('code', $values)->shouldBe([
            'key1' => 'asset1,asset2',
            'key2' => 'asset3',
        ]);
    }

    function it_converts_asset_collection_values_with_paths_from_standard_to_flat_format(
        AttributeColumnsResolver $columnsResolver
    ) {
        $values = [
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
                'data' => ['asset1', 'asset2'],
                'paths' => ['path1', 'path2'],
            ],
            [
                'locale' => 'fr_FR',
                'scope' => 'mobile',
                'data' => ['asset3'],
                'paths' => ['path3'],
            ],
        ];
        $columnsResolver->resolveFlatAttributeName('code', 'en_US', 'ecommerce')->willReturn('key1');
        $columnsResolver->resolveFlatAttributeName('code', 'fr_FR', 'mobile')->willReturn('key2');

        $this->convert('code', $values)->shouldBe([
            'key1' => 'asset1,asset2',
            'key1-file_path' => 'path1,path2',
            'key2' => 'asset3',
            'key2-file_path' => 'path3',
        ]);
    }
}
