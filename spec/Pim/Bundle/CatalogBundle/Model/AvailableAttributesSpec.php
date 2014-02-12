<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AvailableAttributesSpec extends ObjectBehavior
{
    function it_has_attribute_ids()
    {
        $this->setAttributeIds([1, 2, 3]);
        $this->getAttributeIds()->shouldReturn([1, 2, 3]);
    }
}
