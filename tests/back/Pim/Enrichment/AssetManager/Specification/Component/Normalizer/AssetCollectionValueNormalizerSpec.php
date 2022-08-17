<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer\AssetCollectionValueNormalizer;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class AssetCollectionValueNormalizerSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionValueNormalizer::class);
        $this->shouldBeAnInstanceOf(AbstractProductValueNormalizer::class);
    }

    function it_normalize_an_asset_collection_product_value_with_disordered_codes(
        AssetCollectionValue $assetValue,
        GetAttributes $getAttributes,
        Asset $asset1,
        Asset $asset2,
        AssetCode $assetCode1,
        AssetCode $assetCode2
    ) {
        $assetValue->getAttributeCode()->willReturn('asset_attribute');
        $assetValue->getLocaleCode()->willReturn(null);
        $assetValue->getScopeCode()->willReturn(null);

        $getAttributes->forCode('asset_attribute')->willReturn(new Attribute(
            'asset_attribute',
            'pim_catalog_asset_collection',
            [],
            false,
            false,
            null,
            null,
            false,
            AttributeTypes::ASSET_COLLECTION,
            []
        ));

        $assetCode1->__toString()->willReturn('asset1');
        $asset1->getCode()->willReturn($assetCode1);
        $assetCode2->__toString()->willReturn('asset2');
        $asset2->getCode()->willReturn($assetCode2);

        $assetValue->getData()->willReturn(['0' => $assetCode1, '2' => $assetCode2]);

        $this->normalize($assetValue, ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(
                [
                    'asset_attribute-pim_catalog_asset_collection' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['asset1', 'asset2'],
                        ],
                    ],
                ]
            );
    }
}
