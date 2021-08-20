<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Remover;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Remover\ReferenceEntityCollectionRemover;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferenceEntityCollectionRemoverSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attributeValidatorHelper,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->beConstructedWith($attributeValidatorHelper, $entityWithValuesBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityCollectionRemover::class);
        $this->shouldImplement(AttributeRemoverInterface::class);
    }

    function it_only_supports_reference_entity_collection_attributes(
        AttributeInterface $refEntityAttribute,
        AttributeInterface $textAttribute
    ) {
        $refEntityAttribute->getType()->willReturn(AttributeTypes::REFERENCE_ENTITY_COLLECTION);
        $this->supportsAttribute($refEntityAttribute)->shouldReturn(true);
        $textAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $this->supportsAttribute($textAttribute)->shouldReturn(false);
    }

    function it_removes_records_from_a_reference_entity_collection_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('designers');
        $product->getValue('designers', null, null)->willReturn(ReferenceEntityCollectionValue::value('designers', [
            RecordCode::fromString('starck'),
            RecordCode::fromString('newson'),
            RecordCode::fromString('arad'),
            RecordCode::fromString('dyson'),
        ]));

        $entityWithValuesBuilder->addOrReplaceValue($product, $attribute, null, null, ['newson', 'arad'])->shouldBeCalled();
        $this->removeAttributeData($product, $attribute, ['starck', 'dyson'], ['locale' => null, 'scope' => null]);
    }

    function it_removes_data_from_a_scopable_and_localized_reference_entity_collection_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('designers');
        $value = ReferenceEntityCollectionValue::scopableLocalizableValue(
            'designers',
            [
                RecordCode::fromString('newson'),
                RecordCode::fromString('starck'),
                RecordCode::fromString('arad'),
            ],
            'print',
            'fr_FR'
        );
        $product->getValue('designers', 'fr_FR', 'print')->willReturn($value);

        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            'fr_FR',
            'print',
            ['newson', 'arad']
        )->shouldBeCalled();

        $this->removeAttributeData(
            $product,
            $attribute,
            ['dyson', 'starck'],
            ['locale' => 'fr_FR', 'scope' => 'print']
        );
    }

    function it_does_nothing_if_the_entity_does_not_have_a_matching_value(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $product,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('designers');
        $product->getValue('designers', null, null)->willReturn(null);
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->removeAttributeData(
            $product,
            $attribute,
            ['dyson', 'arad'],
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
