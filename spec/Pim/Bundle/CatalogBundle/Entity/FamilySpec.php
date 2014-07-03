<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class FamilySpec extends ObjectBehavior
{
    function it_contains_attributes(AbstractAttribute $sku, AbstractAttribute $name)
    {
        $this->addAttribute($sku)->shouldReturn($this);
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this->hasAttribute($name)->shouldReturn(true);
    }

    function it_allows_removing_attributes(AbstractAttribute $name)
    {
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $this->addAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(true);
        $this->removeAttribute($name)->shouldReturn($this);
        $this->hasAttribute($name)->shouldReturn(false);
    }

    function it_throws_an_exception_when_removing_the_identifier_attribute(AbstractAttribute $sku)
    {
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $this->addAttribute($sku)->shouldReturn($this);
        $this->hasAttribute($sku)->shouldReturn(true);
        $this
            ->shouldThrow(new \InvalidArgumentException('Identifier cannot be removed from a family.'))
            ->duringRemoveAttribute($sku);
        $this->hasAttribute($sku)->shouldReturn(true);
    }

    function it_allows_defining_an_attribute_to_use_as_label(AbstractAttribute $name)
    {
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $this->getAttributeAsLabel()->shouldReturn(null);;
        $this->setAttributeAsLabel($name)->shouldReturn($this);
    }
}
