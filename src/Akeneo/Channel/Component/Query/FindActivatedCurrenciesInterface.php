<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Query;

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
     * @return array
     */
    public function forChannel(string $channelCode): array;

    /**
     * Method that returns a list of all currencies codes activated.
     *
     * @return array
     */
    public function forAllChannels(): array;
}
