<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AttributeRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\FieldRemoverInterface;

class RemoverRegistrySpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_remover_registry()
    {
        $this->shouldImplement(RemoverRegistryInterface::class);
    }

    function it_gets_attribute_remover(
        AttributeInterface $color,
        AttributeInterface $description,
        AttributeInterface $price,
        AttributeRemoverInterface $optionRemover,
        AttributeRemoverInterface $textRemover
    ) {
        $this->register($optionRemover);
        $this->register($textRemover);

        $optionRemover->supportsAttribute($color)->willReturn(true);
        $optionRemover->supportsAttribute($description)->willReturn(false);
        $optionRemover->supportsAttribute($price)->willReturn(false);

        $textRemover->supportsAttribute($description)->willReturn(true);
        $textRemover->supportsAttribute($price)->willReturn(false);

        $this->getAttributeRemover($color)->shouldReturn($optionRemover);
        $this->getAttributeRemover($description)->shouldReturn($textRemover);
        $this->getAttributeRemover($price)->shouldReturn(null);
    }

    function it_gets_field_remover(
        FieldRemoverInterface $categoryRemover,
        FieldRemoverInterface $familyRemover
    ) {
        $this->register($categoryRemover);
        $this->register($familyRemover);

        $categoryRemover->supportsField('category')->willReturn(true);
        $categoryRemover->supportsField('family')->willReturn(false);
        $categoryRemover->supportsField('enabled')->willReturn(false);

        $familyRemover->supportsField('category')->willReturn(false);
        $familyRemover->supportsField('family')->willReturn(true);
        $familyRemover->supportsField('enabled')->willReturn(false);

        $this->getFieldRemover('category')->shouldReturn($categoryRemover);
        $this->getFieldRemover('family')->shouldReturn($familyRemover);
        $this->getFieldRemover('enabled')->shouldReturn(null);
    }
}
