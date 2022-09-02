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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Channel;

use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\Channel\API\Query\FindChannels;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindActivatedLocalesPerChannels implements FindActivatedLocalesPerChannelsInterface
{
    public function __construct(
        private FindChannels $findChannels
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $channels = $this->findChannels->findAll();
        $matrix = [];

        foreach ($channels as $channel) {
            $matrix[$channel->getCode()] = $channel->getLocaleCodes();
        }

        return $matrix;
    }
}
