<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity;

use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    function it_sets_attribute_as_required_if_type_is_identifier()
    {
        $this->isRequired()->shouldReturn(false);
        $this->setType('pim_catalog_identifier');
        $this->isRequired()->shouldReturn(true);
    }
}
