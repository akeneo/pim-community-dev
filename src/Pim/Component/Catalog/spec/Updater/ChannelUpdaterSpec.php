<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;

class ChannelUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $currencyRepository
    ) {
        $this->beConstructedWith($categoryRepository, $localeRepository, $currencyRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\ChannelUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_channel()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "Pim\Component\Catalog\Model\ChannelInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_channel(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        ChannelInterface $channel,
        CategoryInterface $tree,
        LocaleInterface $enUS,
        LocaleInterface $frFR,
        CurrencyInterface $usd,
        CurrencyInterface $eur
    ) {
        $values = [
            'code'  => 'ecommerce',
            'label' => 'Ecommerce',
            'locales'    => ['en_US', 'fr_FR'],
            'currencies' => ['EUR', 'USD'],
            'tree'       => 'master_catalog',
        ];

        $channel->setCode('ecommerce')->shouldBeCalled();
        $channel->setLabel('Ecommerce')->shouldBeCalled();

        $categoryRepository->findOneByIdentifier('master_catalog')->willReturn($tree);
        $channel->setCategory($tree)->shouldBeCalled();

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);
        $channel->addLocale($enUS)->shouldBeCalled();
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $channel->addLocale($frFR)->shouldBeCalled();

        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);
        $channel->addCurrency($eur)->shouldBeCalled();
        $currencyRepository->findOneByIdentifier('USD')->willReturn($usd);
        $channel->addCurrency($usd)->shouldBeCalled();

        $this->update($channel, $values, []);
    }

    function it_throws_an_exception_if_category_not_found(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        ChannelInterface $channel,
        LocaleInterface $frFR,
        CurrencyInterface $eur
    ) {
        $values = [
            'code'  => 'ecommerce',
            'label' => 'Ecommerce',
            'locales'    => ['fr_FR'],
            'currencies' => ['EUR'],
            'tree'       => 'unknown',
        ];
        $categoryRepository->findOneByIdentifier('unknown')->willReturn(null);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Category with "%s" code does not exist', 'unknown')))
            ->during('update', [$channel, $values]);
    }

    function it_throws_an_exception_if_locale_not_found(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        ChannelInterface $channel,
        CategoryInterface $tree,
        CurrencyInterface $eur
    ) {
        $values = [
            'code'  => 'ecommerce',
            'label' => 'Ecommerce',
            'locales'    => ['unknown'],
            'currencies' => ['EUR'],
            'tree'       => 'tree',
        ];
        $categoryRepository->findOneByIdentifier('tree')->willReturn($tree);
        $localeRepository->findOneByIdentifier('unknown')->willReturn(null);
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Locale with "%s" code does not exist', 'unknown')))
            ->during('update', [$channel, $values]);
    }

    function it_throws_an_exception_if_currency_not_found(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        ChannelInterface $channel,
        CategoryInterface $tree,
        LocaleInterface $frFR
    ) {
        $values = [
            'code'  => 'ecommerce',
            'label' => 'Ecommerce',
            'locales'    => ['fr_FR'],
            'currencies' => ['unknown'],
            'tree'       => 'tree',
        ];
        $categoryRepository->findOneByIdentifier('tree')->willReturn($tree);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $currencyRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException(sprintf('Currency with "%s" code does not exist', 'unknown')))
            ->during('update', [$channel, $values]);
    }
}
