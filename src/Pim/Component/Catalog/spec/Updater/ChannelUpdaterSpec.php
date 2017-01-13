<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\ChannelTranslation;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ChannelTranslationInterface;
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
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\ChannelInterface'
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
        CurrencyInterface $eur,
        ChannelTranslationInterface $channelTranslation
    ) {
        $values = [
            'code'             => 'ecommerce',
            'labels'           => [
                'fr_FR' => 'Tablette',
                'en_US' => 'Tablet',
            ],
            'locales'          => ['en_US', 'fr_FR'],
            'currencies'       => ['EUR', 'USD'],
            'category_tree'    => 'master_catalog',
            'conversion_units' => [
                'weight' => 'GRAM'
            ],
        ];

        $channel->setCode('ecommerce')->shouldBeCalled();

        $categoryRepository->findOneByIdentifier('master_catalog')->willReturn($tree);
        $channel->setCategory($tree)->shouldBeCalled();

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $channel->setLocales([$enUS, $frFR])->shouldBeCalled();

        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);
        $currencyRepository->findOneByIdentifier('USD')->willReturn($usd);
        $channel->setCurrencies([$eur, $usd])->shouldBeCalled();

        $channel->setLocale('en_US')->shouldBeCalled();
        $channel->setLocale('fr_FR')->shouldBeCalled();
        $channel->getTranslation()->willReturn($channelTranslation);

        $channelTranslation->setLabel('Tablet');
        $channelTranslation->setLabel('Tablette');

        $channel->setConversionUnits([
            'weight' => 'GRAM'
        ])->shouldBeCalled();

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
            'code'          => 'ecommerce',
            'category_tree' => 'unknown',
            'labels'        => [
                'fr_FR' => 'E-commerce',
            ],
            'locales'       => ['fr_FR'],
            'currencies' => ['EUR']
        ];
        $categoryRepository->findOneByIdentifier('unknown')->willReturn(null);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);

        $channel->setCode('ecommerce')->shouldBeCalled();

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'category_tree',
                'code',
                'The category does not exist',
                'updater',
                'channel',
                'unknown'
            )
        )->during(
            'update',
            [$channel, $values]
        );
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
            'code'          => 'ecommerce',
            'locales'       => ['unknown'],
            'currencies'    => ['EUR'],
            'category_tree' => 'tree',
        ];
        $categoryRepository->findOneByIdentifier('tree')->willReturn($tree);
        $localeRepository->findOneByIdentifier('unknown')->willReturn(null);
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'locales',
                'code',
                'The locale does not exist',
                'updater',
                'channel',
                'unknown'
            )
        )->during(
            'update',
            [$channel, $values]
        );
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
            'code'          => 'ecommerce',
            'locales'       => ['fr_FR'],
            'currencies'    => ['unknown'],
            'category_tree' => 'tree',
            'labels'        => [
                'fr_FR' => 'E-commerce',
            ],
        ];
        $categoryRepository->findOneByIdentifier('tree')->willReturn($tree);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $currencyRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'currencies',
                'code',
                'The currency does not exist',
                'updater',
                'channel',
                'unknown'
            )
        )->during('update', [$channel, $values]);
    }
}
