<?php

namespace spec\Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ValuesContainerBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Model\ValuesContainerInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

class MultiSelectAttributeRemoverSpec extends ObjectBehavior
{
    function let(
        AttributeValidatorHelper $attrValidatorHelper,
        ValuesContainerBuilderInterface $valuesContainerBuilder
    ) {
        $this->beConstructedWith(
            $attrValidatorHelper,
            $valuesContainerBuilder,
            ['pim_catalog_multiselect']
        );
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface');
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

    function it_removes_an_attribute_data_multi_select_value_from_a_values_container(
        $valuesContainerBuilder,
        AttributeInterface $attribute,
        ValuesContainerInterface $valuesContainer,
        ProductValueInterface $productValue,
        ArrayCollection $options,
        AttributeOptionInterface $vneck,
        AttributeOptionInterface $round,
        \ArrayIterator $optionsIterator
    ) {
        $attribute->getCode()->willReturn('tshirt_style');

        $valuesContainer->getValue('tshirt_style', 'fr_FR', 'mobile')->willReturn($productValue);

        $productValue->getData()->willReturn($options);

        $options->getIterator()->willReturn($optionsIterator);
        $optionsIterator->rewind()->shouldBeCalled();
        $optionsIterator->valid()->willReturn(true, true, false);
        $optionsIterator->current()->willReturn($vneck, $round);
        $optionsIterator->next()->shouldBeCalled();

        $round->getCode()->willReturn('round');
        $vneck->getCode()->willReturn('vneck');

        $valuesContainerBuilder->addOrReplaceValue($valuesContainer, $attribute, 'fr_FR', 'mobile', ['round'])->shouldBeCalled();

        $this->removeAttributeData($valuesContainer, $attribute, ['vneck'], ['locale' => 'fr_FR', 'scope' => 'mobile']);
    }

    function it_throws_an_error_if_attribute_data_value_is_not_an_array(
        AttributeInterface $attribute,
        ValuesContainerInterface $valuesContainer
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = 'not an array!';
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover',
                $data
            )
        )->during('removeAttributeData', [$valuesContainer, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }

    function it_throws_an_error_if_attribute_data_value_array_is_not_string(
        AttributeInterface $attribute,
        ValuesContainerInterface $valuesContainer
    ) {
        $attribute->getCode()->willReturn('attributeCode');

        $data = [0];
        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'attributeCode',
                'one of the option codes is not a string, "integer" given',
                'Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover',
                $data
            )
        )->during('removeAttributeData', [$valuesContainer, $attribute, $data, ['locale' => 'fr_FR', 'scope' => 'mobile']]);
    }
}
