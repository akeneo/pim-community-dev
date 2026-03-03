<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Currency;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class InMemoryFindActivatedCurrenciesSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository)
    {
        $eur = (new Currency())->setCode('EUR');
        $usd = (new Currency())->setCode('USD');
        $gbp = (new Currency())->setCode('GBP');

        $ecommerce = (new Channel())->setCode('ecommerce');
        $ecommerce->addCurrency($eur);
        $ecommerce->addCurrency($usd);
        $print = (new Channel())->setCode('print');
        $print->addCurrency($eur);
        $print->addCurrency($gbp);

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);
        $channelRepository->findOneByIdentifier('print')->willReturn($print);
        $channelRepository->findOneByIdentifier(Argument::notIn(['ecommerce', 'print']))->willReturn(null);
        $channelRepository->findAll()->willReturn([$ecommerce, $print]);

        $this->beConstructedWith($channelRepository);
    }

    function it_a_find_activated_currencies_query()
    {
        $this->shouldImplement(FindActivatedCurrenciesInterface::class);
    }

    function it_finds_activated_currencies_for_a_channel()
    {
        $this->forChannel('ecommerce')->shouldReturn(['EUR', 'USD']);
        $this->forChannel('print')->shouldReturn(['EUR', 'GBP']);
        $this->forChannel('unknown_channel')->shouldReturn([]);
    }

    function it_finds_all_activated_currencies()
    {
        $this->forAllChannels()->shouldReturn(['EUR', 'USD', 'GBP']);
    }

    function it_finds_currencies_indexed_by_channel_code()
    {
        $this->forAllChannelsIndexedByChannelCode()->shouldReturn([
            'ecommerce' => ['EUR', 'USD'],
            'print' => ['EUR', 'GBP'],
        ]);
    }
}
