<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Doctrine\Common\Collections\ArrayCollection;

class FamilySpec extends ObjectBehavior
{
    function it_contains_attributes(Attribute $sku, Attribute $name)
    {
        $this->addAttribute($sku)->shouldReturn($this);
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this->hasAttribute($name)->shouldReturn(true);
    }

    function it_throws_an_exception_when_removes_identifier_attribute(Attribute $sku)
    {
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $this->addAttribute($sku)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this->shouldThrow(new \InvalidArgumentException('Identifier cannot be removed from a family.'))->duringRemoveAttribute($sku);
        $this->hasAttribute($sku)->shouldReturn(true);
    }

    function it_removes_attribute(Attribute $name)
    {
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(true);
        $this->removeAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(false);
    }

    function it_defines_attribute_as_label(Attribute $name)
    {
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $this->getAttributeAsLabel()->shouldReturn(null);;
        $this->setAttributeAsLabel($name)->shouldReturn($this);
    }
}
