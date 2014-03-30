<?php

namespace spec\Pim\Bundle\CatalogBundle\Context;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CatalogContextSpec extends ObjectBehavior
{
    function it_throws_a_logic_exception_when_a_configuration_is_missing()
    {
        $exception = new \LogicException(sprintf('"%s" expects to be configured with "%s"', 'Pim\Bundle\CatalogBundle\Context\CatalogContext', 'localeCode'));

        $this->shouldThrow($exception)->duringGetLocaleCode();
    }

    function it_configures_the_locale_code()
    {
        $this->setLocaleCode('fr_FR')->shouldReturn($this);
        $this->getLocaleCode()->shouldReturn('fr_FR');
    }

    function it_configures_the_scope_code()
    {
        $this->setScopeCode('ecommerce')->shouldReturn($this);
        $this->getScopeCode()->shouldReturn('ecommerce');
    }
}

