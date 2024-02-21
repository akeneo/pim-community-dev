<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AttributeSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;

class SetterRegistrySpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_gets_attribute_setter(
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeSetterInterface $optionSetter,
        AttributeSetterInterface $textSetter
    ) {
        $color->getCode()->willReturn('color');
        $description->getCode()->willReturn('description');
        $price->getCode()->willReturn('price');

        $this->register($optionSetter);
        $this->register($textSetter);

        $optionSetter->supportsAttribute($color)->willReturn(true);
        $optionSetter->supportsAttribute($description)->willReturn(false);
        $optionSetter->supportsAttribute($price)->willReturn(false);

        $textSetter->supportsAttribute($description)->willReturn(true);
        $textSetter->supportsAttribute($price)->willReturn(false);

        $this->getAttributeSetter($color)->shouldReturn($optionSetter);
        $this->getAttributeSetter($description)->shouldReturn($textSetter);
        $this->getAttributeSetter($price)->shouldReturn(null);
    }

    function it_gets_field_setter(
        FieldSetterInterface $categorySetter,
        FieldSetterInterface $familySetter
    ) {
        $this->register($categorySetter);
        $this->register($familySetter);

        $categorySetter->supportsField('category')->willReturn(true);
        $categorySetter->supportsField('family')->willReturn(false);
        $categorySetter->supportsField('enabled')->willReturn(false);

        $familySetter->supportsField('category')->willReturn(false);
        $familySetter->supportsField('family')->willReturn(true);
        $familySetter->supportsField('enabled')->willReturn(false);

        $this->getFieldSetter('category')->shouldReturn($categorySetter);
        $this->getFieldSetter('family')->shouldReturn($familySetter);
        $this->getFieldSetter('enabled')->shouldReturn(null);
    }
}
