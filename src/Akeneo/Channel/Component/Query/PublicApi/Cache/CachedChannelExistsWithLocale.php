<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Query\PublicApi\Cache;

use Akeneo\Channel\Component\Query\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedChannelExistsWithLocale implements ChannelExistsWithLocaleInterface
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
