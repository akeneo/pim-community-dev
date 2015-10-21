<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class AvailableAttributesSpec extends ObjectBehavior
{
    function it_has_attributes(\Pim\Component\Catalog\Model\AttributeInterface $sku, \Pim\Component\Catalog\Model\AttributeInterface $name)
    {
        $this->setAttributes([$sku, $name]);
        $this->getAttributes()->shouldReturn([$sku, $name]);
    }
}
