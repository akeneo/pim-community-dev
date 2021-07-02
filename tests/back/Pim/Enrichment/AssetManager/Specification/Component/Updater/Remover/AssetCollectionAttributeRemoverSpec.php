<?php

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Updater\Remover;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\Updater\Remover\AssetCollectionAttributeRemover;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssetCollectionAttributeRemoverSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attrValidatorHelper,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->beConstructedWith($attrValidatorHelper, $entityWithValuesBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionAttributeRemover::class);
        $this->shouldBeAnInstanceOf(AttributeRemoverInterface::class);
    }

    function it_only_supports_asset_collection_attributes(
        AttributeInterface $assetCollectionAttribute,
        AttributeInterface $textAttribute
    ) {
        $assetCollectionAttribute->getType()->willReturn(AttributeTypes::ASSET_COLLECTION);
        $this->supportsAttribute($assetCollectionAttribute)->shouldReturn(true);

        $textAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $this->supportsAttribute($textAttribute)->shouldReturn(false);
    }

    function it_removes_data_from_an_asset_collection_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('asset_collection');
        $assetValue = AssetCollectionValue::value(
            'asset_collection',
            [
                AssetCode::fromString('asset_code_1'),
                AssetCode::fromString('asset_code_2'),
                AssetCode::fromString('asset_code_3'),
                AssetCode::fromString('asset_code_4'),
            ]
        );
        $product->getValue('asset_collection', null, null)->willReturn($assetValue);

        $entityWithValuesBuilder->addOrReplaceValue($product, $attribute, null, null, ['asset_code_1', 'asset_code_3'])
                                ->shouldBeCalled();

        $this->removeAttributeData(
            $product,
            $attribute,
            ['asset_code_2', 'asset_code_4'],
            ['locale' => null, 'scope' => null]
        );
    }

    function it_removes_data_from_a_scopable_and_localized_asset_collection_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('asset_collection');
        $assetValue = AssetCollectionValue::scopableLocalizableValue(
            'asset_collection',
            [
                AssetCode::fromString('asset_code_2'),
                AssetCode::fromString('asset_code_3'),
                AssetCode::fromString('asset_code_1'),
                AssetCode::fromString('asset_code_4'),
            ],
            'ecommerce',
            'en_US'
        );
        $product->getValue('asset_collection', 'en_US', 'ecommerce')->willReturn($assetValue);

        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            'en_US',
            'ecommerce',
            ['asset_code_3', 'asset_code_1']
        )->shouldBeCalled();

        $this->removeAttributeData(
            $product,
            $attribute,
            ['asset_code_2', 'asset_code_4'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );
    }

    function it_does_nothing_if_the_entity_does_not_have_a_matching_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('asset_collection');
        $product->getValue('asset_collection', null, null)->willReturn(null);
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->removeAttributeData(
            $product,
            $attribute,
            ['asset_code_2', 'asset_code_4'],
            ['locale' => null, 'scope' => null]
        );
    }

    function it_throws_an_error_if_attribute_data_value_is_not_an_array_of_strings(
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'removeAttributeData',
            [
                $product,
                $attribute,
                'invalid data',
                ['locale' => null, 'scope' => null],
            ]
        );

        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'removeAttributeData',
            [
                $product,
                $attribute,
                ['valid_code', 123, true],
                ['locale' => null, 'scope' => null],
            ]
        );
    }
}
