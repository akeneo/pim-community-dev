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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Channel;

use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Channel\ChannelExistsInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ChannelExists implements ChannelExistsInterface
{
    public function __construct(
        private FindChannels $findChannels
    ) {
    }

    public function exists(ChannelIdentifier $channelIdentifier): bool
    {
        $existingChannels = $this->findChannels->findAll();
        $channelCode = strtolower($channelIdentifier->normalize());

        foreach ($existingChannels as $existingChannel) {
            if ($channelCode === strtolower($existingChannel->getCode())) {
                return true;
            }
        }

        return false;
    }
}
