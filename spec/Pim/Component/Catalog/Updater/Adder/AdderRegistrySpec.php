<?php

namespace spec\Pim\Component\Catalog\Updater\Adder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface;
use Pim\Component\Catalog\Updater\Adder\FieldAdderInterface;

class AdderRegistrySpec extends ObjectBehavior
{
    function it_gets_attribute_setter(
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeAdderInterface $optionAdder,
        AttributeAdderInterface $textAdder
    ) {
        $color->getCode()->willReturn('color');
        $description->getCode()->willReturn('description');
        $price->getCode()->willReturn('price');

        $this->register($optionAdder);
        $this->register($textAdder);

        $optionAdder->supportsAttribute($color)->willReturn(true);
        $optionAdder->supportsAttribute($description)->willReturn(false);
        $optionAdder->supportsAttribute($price)->willReturn(false);

        $textAdder->supportsAttribute($description)->willReturn(true);
        $textAdder->supportsAttribute($price)->willReturn(false);

        $this->getAttributeAdder($color)->shouldReturn($optionAdder);
        $this->getAttributeAdder($description)->shouldReturn($textAdder);
        $this->getAttributeAdder($price)->shouldReturn(null);
    }

    function it_gets_field_setter(
        FieldAdderInterface $categoryAdder,
        FieldAdderInterface $familyAdder
    ) {
        $this->register($categoryAdder);
        $this->register($familyAdder);

        $categoryAdder->supportsField('category')->willReturn(true);
        $categoryAdder->supportsField('family')->willReturn(false);
        $categoryAdder->supportsField('enabled')->willReturn(false);

        $familyAdder->supportsField('category')->willReturn(false);
        $familyAdder->supportsField('family')->willReturn(true);
        $familyAdder->supportsField('enabled')->willReturn(false);

        $this->getFieldAdder('category')->shouldReturn($categoryAdder);
        $this->getFieldAdder('family')->shouldReturn($familyAdder);
        $this->getFieldAdder('enabled')->shouldReturn(null);
    }
}
