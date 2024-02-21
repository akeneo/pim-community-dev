<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Context;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use PhpSpec\ObjectBehavior;

class CatalogContextSpec extends ObjectBehavior
{
    function it_throws_a_logic_exception_when_a_configuration_is_missing()
    {
        $this
            ->shouldThrow(
                new \LogicException(
                    sprintf(
                        '"%s" expects to be configured with "%s"',
                        CatalogContext::class,
                        'localeCode'
                    )
                )
            )
            ->duringGetLocaleCode();
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

    function it_returns_presence_of_locale_code()
    {
        $this->hasLocaleCode()->shouldReturn(false);
        $this->setConfiguration('localeCode', 'fr_FR');
        $this->hasLocaleCode()->shouldReturn(true);
    }

    function it_returns_presence_of_scope_code()
    {
        $this->hasScopeCode()->shouldReturn(false);
        $this->setConfiguration('scopeCode', 'mobile');
        $this->hasScopeCode()->shouldReturn(true);
    }
}
