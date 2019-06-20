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

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Cache\Channel;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Cache\Channel\CacheChannelExists;
use PhpSpec\ObjectBehavior;

class CacheChannelExistsSpec extends ObjectBehavior
{
    function let(ChannelExistsInterface $channelExists)
    {
        $this->beConstructedWith($channelExists);
    }

    function it_is_a_query_to_determine_if_a_channel_exists()
    {
        $this->shouldImplement(ChannelExistsInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CacheChannelExists::class);
    }

    function it_keeps_in_cache_if_a_channel_exists(CacheChannelExists $channelExists)
    {
        $channelIdentifier = ChannelIdentifier::fromCode('mobile');
        $channelExists->exists($channelIdentifier)
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->exists($channelIdentifier)->shouldReturn(true);
        $this->exists($channelIdentifier)->shouldReturn(true);
    }
}
