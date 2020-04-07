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

namespace Akeneo\Pim\Automation\RuleEngine\tests\back\Acceptance\InMemory;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\ChannelExistsAndBoundToLocaleInterface;
use Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class InMemoryChannelExistsAndBoundToLocale implements ChannelExistsAndBoundToLocaleInterface
{
    /** @var InMemoryChannelRepository */
    private $channelRepository;

    public function __construct(InMemoryChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function doesChannelExist(string $channelCode): bool
    {
        return in_array($channelCode, $this->channelRepository->getChannelCodes());
    }

    public function isLocaleActive(string $localeCode): bool
    {
        foreach ($this->channelRepository->findAll() as $channel) {
            if (in_array($localeCode, $channel->getLocaleCodes())) {
                return true;
            }
        }

        return false;
    }

    public function isLocaleBoundToChannel(string $localeCode, string $channelCode): bool
    {
        foreach ($this->channelRepository->findAll() as $channel) {
            if ($channel->getCode() === $channelCode) {
                return in_array($localeCode, $channel->getLocaleCodes());
            }
        }

        return false;
    }
}
