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
namespace Akeneo\Pim\Automation\RuleEngine\Component\Query\Cache\Channel;

use Akeneo\Pim\Automation\RuleEngine\Component\Query\ChannelExistsAndBoundToLocaleInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Query\GetChannelCodeWithLocaleCodesInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class CachedChannelExistsAndBoundToLocale implements ChannelExistsAndBoundToLocaleInterface
{
    /** @var GetChannelCodeWithLocaleCodesInterface */
    private $getChannelCodeWithLocaleCodes;

    /** @var null|array */
    private $indexedChannelsWithLocales = null;

    public function __construct(GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes)
    {
        $this->getChannelCodeWithLocaleCodes = $getChannelCodeWithLocaleCodes;
    }

    /**
     * {@inheritDoc}
     */
    public function doesChannelExist(string $channelCode): bool
    {
        $this->initializeCache();

        return array_key_exists($channelCode, $this->indexedChannelsWithLocales);
    }

    /**
     * {@inheritDoc}
     */
    public function isLocaleActive(string $localeCode): bool
    {
        $this->initializeCache();

        foreach ($this->indexedChannelsWithLocales as $channelWithLocales) {
            if (in_array($localeCode, $channelWithLocales['localeCodes'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isLocaleBoundToChannel(string $localeCode, string $channelCode): bool
    {
        $this->initializeCache();

        return isset($this->indexedChannelsWithLocales[$channelCode])
            ? in_array($localeCode, $this->indexedChannelsWithLocales[$channelCode]['localeCodes'])
            : false
            ;
    }

    private function initializeCache(): void
    {
        if (null == $this->indexedChannelsWithLocales) {
            $channelsWithLocales = $this->getChannelCodeWithLocaleCodes->findAll();
            foreach ($channelsWithLocales as $channelWithLocales) {
                $this->indexedChannelsWithLocales[$channelWithLocales['channelCode']] = $channelWithLocales;
            }
        }
    }
}
