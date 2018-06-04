<?php

namespace spec\Pim\Component\Enrich\Model;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class AvailableAttributesSpec extends ObjectBehavior
{
    function it_has_attributes(
        \Akeneo\Pim\Structure\Component\Model\AttributeInterface $sku, \Akeneo\Pim\Structure\Component\Model\AttributeInterface $name)
    {
        $this->setAttributes([$sku, $name]);
        $this->getAttributes()->shouldReturn([$sku, $name]);
    }
}
