<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes;

use Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes\LinkedProductAttribute;
use Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes\SearchLinkedProductAttributes;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class SearchLinkedProductAttributesSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        GetAttributeTranslations $getAttributeTranslation
    ) {
        $this->beConstructedWith($getAttributes, $getAttributeTranslation);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchLinkedProductAttributes::class);
    }

    function it_returns_search_result(
        GetAttributes $getAttributes,
        GetAttributeTranslations $getAttributeTranslation
    ) {
        $attributes = [
          'an_attribute_code' => $this->createAttribute('an_attribute_code', 'atmosphere'),
          'another_attribute_code' => $this->createAttribute('another_attribute_code', 'atmosphere'),
          'an_attribute_code_to_remove' => $this->createAttribute('an_attribute_code_to_remove', 'notice'),
        ];
        $labels = [
            'an_attribute_code' => [
                'en_US' => 'label 1'
            ],
            'another_attribute_code' => [
                'en_US' => 'label 2'
            ],
        ];
        $expectedLinkedProductAttribute = [
            $this->createLinkedProductAttribute($attributes['an_attribute_code'], $labels),
            $this->createLinkedProductAttribute($attributes['another_attribute_code'], $labels),
        ];

        $getAttributes->forType('pim_catalog_asset_collection')->willReturn($attributes);
        $getAttributeTranslation->byAttributeCodes(['an_attribute_code', 'another_attribute_code'])->shouldBeCalled()->willReturn($labels);

        $this->searchByAssetFamilyIdentifier(AssetFamilyIdentifier::fromString('atmosphere'))
            ->shouldBeLike($expectedLinkedProductAttribute);
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
            [],
            true
        );
    }

    private function createLinkedProductAttribute(Attribute $attribute, array $labels)
    {
        return new LinkedProductAttribute(
            $attribute->code(),
            $attribute->type(),
            $labels[$attribute->code()] ?? [],
            $attribute->properties()['reference_data_name'],
            $attribute->useableAsGridFilter()
        );
    }
}
