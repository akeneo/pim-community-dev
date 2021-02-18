<?php

namespace Specification\Akeneo\Pim\Structure\Component\Model;

use PhpSpec\ObjectBehavior;

class AttributeSpec extends ObjectBehavior
{
    function it_sets_attribute_as_required_if_type_is_identifier()
    {
        $this->isRequired()->shouldReturn(false);
        $this->setType('pim_catalog_identifier');
        $this->isRequired()->shouldReturn(true);
    }

    function it_returns_the_descriptions()
    {
        $this->addDescription('en_US', 'the description');
        $this->addDescription('fr_FR', 'la description');
        $this->getDescriptions()->shouldReturn([
            'en_US' => 'the description',
            'fr_FR' => 'la description',
        ]);
    }
}
