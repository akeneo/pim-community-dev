<?php

namespace spec\Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FlexibleQueryBuilderSpec extends ObjectBehavior
{
    function it_has_a_locale()
    {
        $this->setLocale('fr');
        $this->getLocale()->shouldReturn('fr');
    }

    function it_has_a_scope()
    {
        $this->setScope('ecommerce');
        $this->getScope()->shouldReturn('ecommerce');
    }
}
