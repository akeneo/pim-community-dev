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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Channel;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Channel\ChannelExistsInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CacheChannelExists implements ChannelExistsInterface
{
    /** @var array<string, bool> */
    private array $channels = [];

    public function __construct(private ChannelExistsInterface $channelExists)
    {
    }

    public function exists(ChannelIdentifier $channelIdentifier): bool
    {
        $channel = $channelIdentifier->normalize();
        if (!array_key_exists($channel, $this->channels)) {
            $this->channels[$channel] = $this->channelExists->exists($channelIdentifier);
        }

        return $this->channels[$channel];
    }
}
