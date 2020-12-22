<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Component\Query\PublicApi\Cache;

use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Channel\Component\Query\PublicApi\Cache\CachedChannelExistsWithLocale;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use PhpSpec\ObjectBehavior;

class CachedChannelExistsWithLocaleSpec extends ObjectBehavior
{
    function let(GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes)
    {
        $getChannelCodeWithLocaleCodes->findAll()->willReturn([
            [
                'channelCode' => 'ecommerce',
                'localeCodes' => ['en_US', 'fr_FR'],
            ],
            [
                'channelCode' => 'mobile',
                'localeCodes' => ['de_DE'],
            ],
            [
                'channelCode' => 'print',
                'localeCodes' => [],
            ],
        ]);
        $this->beConstructedWith($getChannelCodeWithLocaleCodes);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CachedChannelExistsWithLocale::class);
        $this->shouldImplement(ChannelExistsWithLocaleInterface::class);
    }

    function it_tests_the_channel_exist()
    {
        $this->doesChannelExist('ecommerce')->shouldReturn(true);
        $this->doesChannelExist('mobile')->shouldReturn(true);
        $this->doesChannelExist('print')->shouldReturn(true);
        $this->doesChannelExist('other')->shouldReturn(false);
    }

    function it_tests_the_locale_is_active()
    {
        $this->isLocaleActive('fr_FR')->shouldReturn(true);
        $this->isLocaleActive('en_US')->shouldReturn(true);
        $this->isLocaleActive('de_DE')->shouldReturn(true);
        $this->isLocaleActive('es_ES')->shouldReturn(false);
    }

    function it_tests_the_locale_is_bound_to_locale()
    {
        $this->isLocaleBoundToChannel('fr_FR', 'ecommerce')->shouldReturn(true);
        $this->isLocaleBoundToChannel('en_US', 'ecommerce')->shouldReturn(true);
        $this->isLocaleBoundToChannel('de_DE', 'ecommerce')->shouldReturn(false);

        $this->isLocaleBoundToChannel('de_DE', 'mobile')->shouldReturn(true);
        $this->isLocaleBoundToChannel('en_US', 'mobile')->shouldReturn(false);

        $this->isLocaleBoundToChannel('en_US', 'print')->shouldReturn(false);

        $this->isLocaleBoundToChannel('en_US', 'unknown')->shouldReturn(false);
        $this->isLocaleBoundToChannel('unknown', 'unknown')->shouldReturn(false);
    }
}
