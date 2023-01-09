<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryFindActivatedCurrencies implements FindActivatedCurrenciesInterface
{
    public function __construct(private ChannelRepositoryInterface $channelRepository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function forChannel(string $channelCode): array
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            return [];
        }

        return $channel->getCurrencies()->map(
            static fn (CurrencyInterface $currency): string => $currency->getCode()
        )->getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function forAllChannels(): array
    {
        $currencies = [];
        foreach ($this->channelRepository->findAll() as $channel) {
            foreach ($channel->getCurrencies()->toArray() as $currency) {
                $currencies[] = $currency->getCode();
            }
        }

        return \array_values(\array_unique($currencies));
    }

    /**
     * {@inheritdoc}
     */
    public function forAllChannelsIndexedByChannelCode(): array
    {
        $currenciesByChannel = [];
        foreach ($this->channelRepository->findAll()as $channel) {
            $currenciesByChannel[$channel->getCode()] = $channel->getCurrencies()->map(
                static fn (CurrencyInterface $currency): string => $currency->getCode()
            )->getValues();
        }

        return $currenciesByChannel;
    }
}
