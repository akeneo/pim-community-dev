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

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\InMemory;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Test\Acceptance\Channel\InMemoryChannelRepository;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class InMemoryFindActivatedCurrencies implements FindActivatedCurrenciesInterface
{
    /** @var InMemoryChannelRepository */
    private $channelRepository;

    public function __construct(InMemoryChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function forChannel(string $channelCode): array
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            return [];
        }

        $currencies = [];
        /** @var CurrencyInterface $currency */
        foreach ($channel->getCurrencies()->toArray() as $currency) {
            $currencies[$currency->getCode()] = $currency;
        }

        return $currencies;
    }

    public function forAllChannels(): array
    {
        $currencies = [];
        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            /** @var CurrencyInterface $currency */
            foreach ($channel->getCurrencies()->toArray() as $currency) {
                $currencies[$currency->getCode()] = $currency;
            }
        }

        return $currencies;
    }
}
