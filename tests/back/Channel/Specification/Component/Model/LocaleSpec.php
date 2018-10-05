<?php

namespace Specification\Akeneo\Channel\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\Locale;
use PhpSpec\ObjectBehavior;

class LocaleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Locale::class);
    }

    function it_contains_channels(ChannelInterface $ecommerce, ChannelInterface $mobile)
    {
        $this->addChannel($ecommerce)->shouldReturn($this);
        $this->addChannel($mobile)->shouldReturn($this);
        $this->hasChannel($ecommerce)->shouldReturn(true);
        $this->hasChannel($mobile)->shouldReturn(true);
    }

    function it_allows_removing_channels(ChannelInterface $ecommerce)
    {
        $ecommerce->getCode()->willReturn('ecommerce');
        $this->addChannel($ecommerce)->shouldReturn($this);
        $this->hasChannel($ecommerce)->shouldReturn(true);
        $this->removeChannel($ecommerce)->shouldReturn($this);
        $this->hasChannel($ecommerce)->shouldReturn(false);
    }

    function it_returns_default_locale_status()
    {
        $this->isActivated()->shouldReturn(false);
    }

    function it_returns_language()
    {
        $this->setCode('en_US');
        $this->getLanguage()->shouldReturn('en');
    }

    function it_returns_empty_language()
    {
        $this->getLanguage()->shouldReturn(null);
    }

    function it_returns_locale_full_name()
    {
        $this->setCode('en_US');
        $this->getName()->shouldReturn('English (United States)');
    }

    function it_returns_empty_locale_name()
    {
        $this->getName()->shouldReturn(null);
    }
}
