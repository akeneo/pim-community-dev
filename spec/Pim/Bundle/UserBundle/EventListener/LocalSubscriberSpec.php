<?php

namespace spec\Pim\Bundle\UserBundle\EventListener;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class LocalSubscriberSpec extends ObjectBehavior
{
    function let(LocaleSettings $localeSettings)
    {
        $this->beConstructedWith($localeSettings);
    }

    function it_implements_an_event_listener_interface()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_sets_request_if_local_request_is_null($localeSettings, Request $request, ParameterBag $parameterBag)
    {
        $parameterBag->get('_locale')->willReturn(false);
        $request->attributes = $parameterBag;

        $localeSettings->getLanguage()->willReturn('fr_FR');
        $localeSettings->getLocale()->willReturn('fr_FR');
        $request->setLocale('fr_FR')->shouldBeCalled();

        $this->setRequest($request);
    }

    function it_sets_request($localeSettings, Request $request, ParameterBag $parameterBag)
    {
        $parameterBag->get('_locale')->willReturn(true);
        $request->attributes = $parameterBag;

        $localeSettings->getLanguage()->shouldNotBeCalled();
        $localeSettings->getLocale()->willReturn('fr_FR');

        $this->setRequest($request);
    }
}
