<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Channel;

use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Channel\CacheFindActivatedLocalesPerChannels;
use PhpSpec\ObjectBehavior;

class CacheFindActivatedLocalesPerChannelsSpec extends ObjectBehavior
{
    function let(FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels)
    {
        $this->beConstructedWith($findActivatedLocalesPerChannels);
    }

    function it_is_a_query_to_find_activated_locales_per_channels()
    {
        $this->shouldImplement(FindActivatedLocalesPerChannelsInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CacheFindActivatedLocalesPerChannels::class);
    }

    function it_keeps_in_cache_the_activated_locales_per_channels_found($findActivatedLocalesPerChannels)
    {
        $activatedLocalesPerChannels = [
            'ecommerce' => ['fr_FR', 'en_US'],
            'mobile'    => ['en_US'],
        ];

        $findActivatedLocalesPerChannels->__invoke()
            ->shouldBeCalledOnce()
            ->willReturn($activatedLocalesPerChannels);

        $this->__invoke()->shouldReturn($activatedLocalesPerChannels);
        $this->__invoke()->shouldReturn($activatedLocalesPerChannels);
    }
}
