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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Cache\Channel;

use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CacheFindActivatedLocalesPerChannels implements FindActivatedLocalesPerChannelsInterface
{
    private ?array $activatedLocalesPerChannels = null;

    private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels;

    public function __construct(FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels)
    {
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    public function findAll(): array
    {
        if (null === $this->activatedLocalesPerChannels) {
            $this->activatedLocalesPerChannels = $this->findActivatedLocalesPerChannels->findAll();
        }

        return $this->activatedLocalesPerChannels;
    }
}
