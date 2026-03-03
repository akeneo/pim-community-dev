<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Cache;

use Akeneo\Channel\API\Query\GetCaseSensitiveChannelCodeInterface;
use Akeneo\Channel\API\Query\GetCaseSensitiveLocaleCodeInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CachedChannelExistsWithLocale implements ChannelExistsWithLocaleInterface, CachedQueryInterface, GetCaseSensitiveLocaleCodeInterface, GetCaseSensitiveChannelCodeInterface
{
    /**
     * Contains the list of lowercase activated locale codes for each existing lowercase channel
     * Example: [
     *   'ecommerce' => ['en_us', 'fr_fr'],
     *   'mobile' => ['de_de', 'fr_fr'],
     * ]
     *
     * @var null|array<string, string[]>
     */
    private ?array $indexedChannelsWithLocales = null;

    /**
     * Contains the mapping of the lowercase version of each activated locale code to the original one
     * Example: [
     *   'fr_fr' => 'fr_FR',
     *   'de_de' => 'de_DE',
     *   'en_us' => 'en_US',
     * ]
     *
     * @var null|array<string, string>
     */
    private ?array $indexedLocales = null;

    /**
     * Contains the mapping of the lowercase version of each channel code to the original one
     * Example: [
     *   'ecommerce' => 'eCommerce',
     *   'mobile' => 'mobile',
     * ]
     *
     * @var null|array<string, string>
     */
    private ?array $indexedChannels = null;

    public function __construct(
        private GetChannelCodeWithLocaleCodesInterface $getChannelCodeWithLocaleCodes
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function doesChannelExist(string $channelCode): bool
    {
        $this->initializeCache();

        return array_key_exists(\mb_strtolower($channelCode), $this->indexedChannels);
    }

    /**
     * {@inheritDoc}
     */
    public function isLocaleActive(string $localeCode): bool
    {
        $this->initializeCache();
        Assert::isArray($this->indexedLocales);

        return \array_key_exists(\mb_strtolower($localeCode), $this->indexedLocales);
    }

    /**
     * {@inheritDoc}
     */
    public function isLocaleBoundToChannel(string $localeCode, string $channelCode): bool
    {
        $this->initializeCache();
        Assert::isArray($this->indexedChannelsWithLocales);

        return \array_key_exists(\mb_strtolower($channelCode), $this->indexedChannelsWithLocales) &&
            \in_array(\mb_strtolower($localeCode), $this->indexedChannelsWithLocales[\mb_strtolower($channelCode)]);
    }

    public function forLocaleCode(string $localeCode): string
    {
        $this->initializeCache();
        Assert::isArray($this->indexedLocales);
        $lowercaseLocaleCode = \mb_strtolower($localeCode);

        if (!\array_key_exists($lowercaseLocaleCode, $this->indexedLocales)) {
            throw new \LogicException(sprintf('Locale "%s" does not exist or is not activated.', $localeCode));
        }

        return $this->indexedLocales[$lowercaseLocaleCode];
    }

    public function forChannelCode(string $channelCode): string
    {
        $this->initializeCache();
        Assert::isArray($this->indexedChannels);
        $lowercaseChannelCode = \mb_strtolower($channelCode);

        if (!\array_key_exists($lowercaseChannelCode, $this->indexedChannels)) {
            throw new \LogicException(sprintf('Channel "%s" does not exist.', $channelCode));
        }

        return $this->indexedChannels[$lowercaseChannelCode];
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
     * As it never occurs in real use case (except tests), it will not impact performance
     */
    public function clearCache(): void
    {
        $this->indexedChannelsWithLocales = null;
        $this->indexedLocales = null;
        $this->indexedChannels = null;
    }

    private function initializeCache(): void
    {
        if (null === $this->indexedChannelsWithLocales) {
            $channelsWithLocales = $this->getChannelCodeWithLocaleCodes->findAll();
            foreach ($channelsWithLocales as $channelWithLocales) {
                $channelCode = $channelWithLocales['channelCode'];
                $lowercaseChannelCode = \mb_strtolower($channelCode);
                $this->indexedChannels[$lowercaseChannelCode] = $channelCode;
                $localeCodes = $channelWithLocales['localeCodes'];
                foreach ($localeCodes as $localeCode) {
                    $this->indexedLocales[\mb_strtolower($localeCode)] = $localeCode;
                }
                $lowercaseLocaleCodes = \array_map(static fn (string $localeCode): string => \mb_strtolower($localeCode), $localeCodes);
                $this->indexedChannelsWithLocales[$lowercaseChannelCode] = $lowercaseLocaleCodes;
            }
        }
    }
}
