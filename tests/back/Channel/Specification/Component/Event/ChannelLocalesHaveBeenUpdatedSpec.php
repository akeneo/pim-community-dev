<?php

namespace Specification\Akeneo\Channel\Component\Event;

use Akeneo\Channel\Component\Event\ChannelLocalesHaveBeenUpdated;
use PhpSpec\ObjectBehavior;

class ChannelLocalesHaveBeenUpdatedSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('mobile', ['fr_FR', 'en_US', 'de_DE'], ['en_US']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelLocalesHaveBeenUpdated::class);
    }

    function it_returns_deleted_locale_codes_whatever_locales_order()
    {
        $this->deletedLocaleCodes()->shouldReturn(['de_DE', 'fr_FR']);
    }
}
