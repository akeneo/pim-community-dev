<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\MultiSelectAttributeRemover;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;

class MultiSelectAttributeRemoverSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attrValidatorHelper,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $entityWithValuesBuilder,
            ['pim_catalog_multiselect']
        );
    }

    function it_is_a_remover()
    {
        $this->shouldImplement(AttributeRemoverInterface::class);
    }

    function it_supports_multiselect_attributes(
        AttributeInterface $multiSelectAttribute,
        AttributeInterface $textareaAttribute
    ) {
        $multiSelectAttribute->getType()->willReturn('pim_catalog_multiselect');
        $this->supportsAttribute($multiSelectAttribute)->shouldReturn(true);

        $textareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttribute($textareaAttribute)->shouldReturn(false);
    }

    function it_removes_an_attribute_data_multi_select_value_from_an_entity_with_values(
        $entityWithValuesBuilder,
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues,
        ValueInterface $value
    ) {
        $attribute->getCode()->willReturn('tshirt_style');

        $entityWithValues->getValue('tshirt_style', 'fr_FR', 'mobile')->willReturn($value);

        $value->getData()->willReturn(['round', 'vneck']);

        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, 'fr_FR', 'mobile', ['round'])->shouldBeCalled();

        $this->removeAttributeData($entityWithValues, $attribute, ['vneck'], ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_value_is_not_an_array(
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not an array!';
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributeCode',
                MultiSelectAttributeRemover::class,
                $data
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_array_is_not_string(
        AttributeInterface $attribute,
        EntityWithValuesInterface $entityWithValues
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [0];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'attributeCode',
                'one of the option codes is not a string, "integer" given',
                MultiSelectAttributeRemover::class,
                $data
            )
        )->during('removeAttributeData', [$entityWithValues, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }
}
