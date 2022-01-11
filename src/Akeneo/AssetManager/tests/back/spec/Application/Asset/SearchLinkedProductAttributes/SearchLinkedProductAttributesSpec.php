<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes;

use Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes\SearchLinkedProductAttributes;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class SearchLinkedProductAttributesSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchLinkedProductAttributes::class);
    }

    function it_returns_search_result(
        GetAttributes $getAttributes
    ) {
        $attributes = [
          'an_attribute_code' => $this->createAttribute('an_attribute_code', 'atmosphere'),
          'another_attribute_code' => $this->createAttribute('another_attribute_code', 'atmosphere'),
          'an_attribute_code_to_remove' => $this->createAttribute('an_attribute_code_to_remove', 'notice'),
        ];
        $expectedAttributes = [
            'an_attribute_code' => $this->createAttribute('an_attribute_code', 'atmosphere'),
            'another_attribute_code' => $this->createAttribute('another_attribute_code', 'atmosphere'),
        ];

        $getAttributes->forType('pim_catalog_asset_collection')->willReturn($attributes);

        $this->searchByAssetFamilyIdentifier(AssetFamilyIdentifier::fromString('atmosphere'))
            ->shouldBeLike($expectedAttributes);
    }

    private function createAttribute(string $attributeCode, string $assetFamilyIdentifier): Attribute
    {
        return new Attribute(
            $attributeCode,
            'pim_catalog_asset_collection',
            ['reference_data_name' => $assetFamilyIdentifier],
            false,
            false,
            null,
            null,
            null,
            'Akeneo\Pim\Enrichment\Component\Product\Model\Product',
            []
        );
    }
}
