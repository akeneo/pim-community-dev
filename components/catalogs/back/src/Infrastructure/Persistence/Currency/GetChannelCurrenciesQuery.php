<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Currency;

use Akeneo\Catalogs\Application\Persistence\Currency\GetChannelCurrenciesQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelCurrenciesQuery implements GetChannelCurrenciesQueryInterface
{
    public function __construct(private readonly ChannelRepositoryInterface $channelRepository)
    {
    }

    public function execute(string $channelCode): array
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw new \InvalidArgumentException("Channel '$channelCode' not found");
        }

        /** @var array<CurrencyInterface> $currencies */
        $currencies = $channel->getCurrencies()->toArray();

        return \array_map(static fn (CurrencyInterface $currency): string => $currency->getCode(), $currencies);
    }
}
