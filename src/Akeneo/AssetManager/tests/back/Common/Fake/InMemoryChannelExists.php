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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryChannelExists implements ChannelExistsInterface
{
    /** @var ChannelIdentifier[] */
    private array $channels = [];

    public function exists(ChannelIdentifier $channelIdentifier): bool
    {
        foreach ($this->channels as $existingChannel) {
            if ($existingChannel->equals($channelIdentifier)) {
                return true;
            }
        }

        return false;
    }

    public function save(ChannelIdentifier $channelIdentifier): void
    {
        $this->channels[] = $channelIdentifier;
    }
}
