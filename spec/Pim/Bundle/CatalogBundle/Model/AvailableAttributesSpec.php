<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class AvailableAttributesSpec extends ObjectBehavior
{
    function it_has_attributes(AbstractAttribute $sku, AbstractAttribute $name)
    {
        $this->setAttributes([$sku, $name]);
        $this->getAttributes()->shouldReturn([$sku, $name]);
    }
}
