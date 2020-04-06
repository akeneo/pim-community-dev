<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Query\Cache\Channel;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\Cache\Channel\CachedChannelExistsAndBoundToLocale;
use Akeneo\Pim\Automation\RuleEngine\Component\Query\ChannelExistsAndBoundToLocaleInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetChannelCodeWithLocaleCodesInterface;
use PhpSpec\ObjectBehavior;

class CachedChannelExistsAndBoundToLocaleSpec extends ObjectBehavior
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
        $this->shouldHaveType(CachedChannelExistsAndBoundToLocale::class);
        $this->shouldImplement(ChannelExistsAndBoundToLocaleInterface::class);
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
