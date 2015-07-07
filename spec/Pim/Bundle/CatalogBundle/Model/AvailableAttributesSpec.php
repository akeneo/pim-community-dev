<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class AvailableAttributesSpec extends ObjectBehavior
{
    function it_has_attributes(AttributeInterface $sku, AttributeInterface $name)
    {
        $this->setAttributes([$sku, $name]);
        $this->getAttributes()->shouldReturn([$sku, $name]);
    }
}
