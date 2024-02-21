<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Component\Query\PublicApi;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindActivatedCurrenciesInterface
{
    /**
     * Method that returns a list of currencies codes activated for the given channel.
     *
     * @param string $channelCode
     *
     * @return string[]
     */
    public function forChannel(string $channelCode): array;

    /**
     * Method that returns a list of all currencies codes activated.
     *
     * @return string[]
     */
    public function forAllChannels(): array;

    /**
     * Returns currency codes activated for each channel, indexed by channel code
     *
     * @return array<string, string[]>
     */
    public function forAllChannelsIndexedByChannelCode(): array;
}
