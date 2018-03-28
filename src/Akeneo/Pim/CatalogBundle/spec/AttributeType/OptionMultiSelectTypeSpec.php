<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class OptionMultiSelectTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_OPTIONS);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_multiselect');
    }
}
