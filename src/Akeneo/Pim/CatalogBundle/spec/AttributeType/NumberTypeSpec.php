<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class NumberTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_DECIMAL);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_number');
    }
}
