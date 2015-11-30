<?php

namespace spec\Pim\Component\Catalog\Model;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class AvailableAttributesSpec extends ObjectBehavior
{
    function it_has_attributes(AttributeInterface $sku, AttributeInterface $name)
    {
        $this->setAttributes([$sku, $name]);
        $this->getAttributes()->shouldReturn([$sku, $name]);
    }
}
