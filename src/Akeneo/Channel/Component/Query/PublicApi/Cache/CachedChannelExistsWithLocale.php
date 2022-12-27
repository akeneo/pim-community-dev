<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Query\PublicApi\Cache;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Channel\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedChannelExistsWithLocale implements ChannelExistsWithLocaleInterface, CachedQueryInterface
{
    private GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes;

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
        $channelCodes = \array_map(
            '\mb_strtolower',
            \array_column($this->indexedChannelsWithLocales, 'channelCode')
        );

        return in_array(\mb_strtolower($channelCode), $channelCodes);
    }

    /**
     * {@inheritDoc}
     */
    public function isLocaleActive(string $localeCode): bool
    {
        $this->initializeCache();
        $activeLocales = \array_merge(...\array_column($this->indexedChannelsWithLocales, 'localeCodes'));

        return \in_array(\mb_strtolower($localeCode), \array_map('mb_strtolower', $activeLocales));
    }

    /**
     * {@inheritDoc}
     */
    public function isLocaleBoundToChannel(string $localeCode, string $channelCode): bool
    {
        $this->initializeCache();

        foreach ($this->indexedChannelsWithLocales as $cache) {
            if (\mb_strtolower($channelCode) === \mb_strtolower($cache['channelCode'])) {
                return \in_array(\mb_strtolower($localeCode), \array_map('mb_strtolower', $cache['localeCodes']));
            }
        }

        return false;
    }

    /**
     * The goal of this function is to clear the cache of activated locale for a given channel.
     * To tackle some test use case like this one:
     * - load a catalog with activated locale fr_FR for ecommerce
     * - it warmups this cache
     * - then activate the locale en_US for ecommerce
     * - if this cache is not cleared, then en_US is not considered activated when querying with this service
     *
     * The correct way to handle that is to clear the cache after saving a channel.
     * As it never occur in real use case (except tests), it will not impact performance
     */
    public function clearCache(): void
    {
        $this->indexedChannelsWithLocales = null;
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

    public function getLocaleNameWithRightCase(string $locale): string | null
    {
        $this->initializeCache();

        $activeLocales = \array_merge(...\array_column($this->indexedChannelsWithLocales, 'localeCodes'));

        $array = array_filter($activeLocales, fn ($loc) => mb_strtolower($loc) === mb_strtolower($locale));
        return reset($array) ?? null;
    }
}
