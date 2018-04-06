<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Component\Catalog\Model\AttributeInterface;

class FamilySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Family::class);
    }

    function it_contains_attributes(AttributeInterface $sku, AttributeInterface $name)
    {
        $this->addAttribute($sku)->shouldReturn($this);
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this->hasAttribute($name)->shouldReturn(true);
    }

    function it_allows_removing_attributes(AttributeInterface $name)
    {
        $name->getType()->willReturn('pim_catalog_text');
        $name->getCode()->willReturn('code1');
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(true);
        $this->removeAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(false);
    }

    function it_throws_an_exception_when_removing_the_identifier_attribute(AttributeInterface $sku)
    {
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->getCode()->willReturn('code1');
        $this->addAttribute($sku)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this
            ->shouldThrow(new \InvalidArgumentException('Identifier cannot be removed from a family.'))
            ->duringRemoveAttribute($sku);
        $this->hasAttribute($sku)->shouldReturn(true);
    }

    function it_allows_defining_an_attribute_to_use_as_label(AttributeInterface $name)
    {
        $name->getCode()->willReturn('pim_catalog_text');
        $this->getAttributeAsLabel()->shouldReturn(null);
        $this->setAttributeAsLabel($name)->shouldReturn($this);
    }
}
