<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\FieldCopierInterface;

class CopierRegistrySpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_gets_attribute_copier(
        AttributeInterface $fromAttribute1,
        AttributeInterface $fromAttribute2,
        AttributeInterface $fromAttribute3,
        AttributeInterface $toAttribute1,
        AttributeInterface $toAttribute2,
        AttributeInterface $toAttribute3,
        AttributeCopierInterface $copier1,
        AttributeCopierInterface $copier2
    ) {
        $fromAttribute2->getCode()->willReturn('fromAttribute2Code');
        $toAttribute3->getCode()->willReturn('toAttribute3Code');

        $copier1->supportsAttributes($fromAttribute1, $toAttribute1)->willReturn(true);
        $copier1->supportsAttributes($fromAttribute2, $toAttribute2)->willReturn(false);
        $copier1->supportsAttributes($fromAttribute3, $toAttribute3)->willReturn(false);

        $copier2->supportsAttributes($fromAttribute1, $toAttribute1)->willReturn(false);
        $copier2->supportsAttributes($fromAttribute2, $toAttribute2)->willReturn(true);
        $copier2->supportsAttributes($fromAttribute3, $toAttribute3)->willReturn(false);

        $this->register($copier1);
        $this->register($copier2);

        $this->getAttributeCopier($fromAttribute1, $toAttribute1)->shouldReturn($copier1);
        $this->getAttributeCopier($fromAttribute2, $toAttribute2)->shouldReturn($copier2);
        $this->getAttributeCopier($fromAttribute3, $toAttribute3)->shouldReturn(null);
    }

    function it_gets_field_copier(
        FieldCopierInterface $familyCopier,
        FieldCopierInterface $categoryCopier
    ) {
        $familyCopier->supportsFields('family', 'family')->willReturn(true);
        $familyCopier->supportsFields('category', 'category')->willReturn(false);
        $familyCopier->supportsFields('enabled', 'enabled')->willReturn(false);

        $categoryCopier->supportsFields('family', 'family')->willReturn(false);
        $categoryCopier->supportsFields('category', 'category')->willReturn(true);
        $categoryCopier->supportsFields('enabled', 'enabled')->willReturn(false);

        $this->register($familyCopier);
        $this->register($categoryCopier);

        $this->getFieldCopier('family', 'family')->shouldReturn($familyCopier);
        $this->getFieldCopier('category', 'category')->shouldReturn($categoryCopier);
        $this->getFieldCopier('enabled', 'enabled')->shouldReturn(null);
    }
}
