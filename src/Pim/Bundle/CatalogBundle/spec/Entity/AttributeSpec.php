<?php

namespace spec\Pim\Bundle\CatalogBundle\Entity;

use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    function it_sets_attribute_as_required_if_type_is_identifier()
    {
        $this->isRequired()->shouldReturn(false);
        $this->setAttributeType('pim_catalog_identifier');
        $this->isRequired()->shouldReturn(true);
    }

    function it_is_displayable()
    {
        $this->setIsDisplayable(true)->shouldReturn($this);
        $this->isDisplayable()->shouldReturn(true);
        $this->getProperty('is_displayable')->shouldReturn(true);
    }
}
