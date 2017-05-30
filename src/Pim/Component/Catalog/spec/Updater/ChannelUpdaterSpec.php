<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\ChannelTranslationInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

class ChannelUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $currencyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureManager $measureManager
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $localeRepository,
            $currencyRepository,
            $attributeRepository,
            $measureManager
        );
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
        $attributeRepository,
        $measureManager,
        ChannelInterface $channel,
        CategoryInterface $tree,
        LocaleInterface $enUS,
        LocaleInterface $frFR,
        CurrencyInterface $usd,
        CurrencyInterface $eur,
        ChannelTranslationInterface $channelTranslation,
        AttributeInterface $maximumDiagonalAttribute,
        AttributeInterface $weightAttribute
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
                'maximum_diagonal' => 'INCHES',
                'weight'           => 'KILOGRAM',
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

        $maximumDiagonalAttribute->getMetricFamily()->willReturn('Length');
        $weightAttribute->getMetricFamily()->willReturn('Weight');

        $attributeRepository->findOneByIdentifier('maximum_diagonal')->willReturn($maximumDiagonalAttribute);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weightAttribute);

        $measureManager->unitCodeExistsInFamily('INCHES', 'Length')->willReturn(true);
        $measureManager->unitCodeExistsInFamily('KILOGRAM', 'Weight')->willReturn(true);

        $channelTranslation->setLabel('Tablet');
        $channelTranslation->setLabel('Tablette');
        $channel->setConversionUnits([
            'maximum_diagonal' => 'INCHES',
            'weight'           => 'KILOGRAM',
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
                'Pim\Component\Catalog\Updater\ChannelUpdater',
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
                'Pim\Component\Catalog\Updater\ChannelUpdater',
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
                'Pim\Component\Catalog\Updater\ChannelUpdater',
                'unknown'
            )
        )->during('update', [$channel, $values]);
    }

    function it_throws_an_exception_if_conversion_unit_attribute_does_not_exist(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        $attributeRepository,
        ChannelInterface $channel,
        CategoryInterface $tree,
        LocaleInterface $frFR,
        CurrencyInterface $eur
    ) {
        $values = [
            'code'             => 'ecommerce',
            'locales'          => ['fr_FR'],
            'currencies'       => ['EUR'],
            'conversion_units' => ['unknown_attribute' => 'INCHES'],
            'category_tree'    => 'tree',
            'labels'           => [
                'fr_FR' => 'E-commerce',
            ],
        ];
        $categoryRepository->findOneByIdentifier('tree')->willReturn($tree);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);
        $attributeRepository->findOneByIdentifier('unknown_attribute')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'conversionUnits',
                'attributeCode',
                'the attribute code for the conversion unit does not exist',
                'Pim\Component\Catalog\Updater\ChannelUpdater',
                'unknown_attribute'
            )
        )->during('update', [$channel, $values]);
    }

    function it_throws_an_exception_if_conversion_unit_metric_code_does_not_exist(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        $attributeRepository,
        $measureManager,
        ChannelInterface $channel,
        CategoryInterface $tree,
        LocaleInterface $frFR,
        CurrencyInterface $eur,
        AttributeInterface $maximumDiagonalAttribute
    ) {
        $values = [
            'code'             => 'ecommerce',
            'locales'          => ['fr_FR'],
            'currencies'       => ['EUR'],
            'conversion_units' => ['maximum_diagonal' => 'unknown_unit_code'],
            'category_tree'    => 'tree',
            'labels'           => [
                'fr_FR' => 'E-commerce',
            ],
        ];
        $categoryRepository->findOneByIdentifier('tree')->willReturn($tree);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $currencyRepository->findOneByIdentifier('EUR')->willReturn($eur);

        $maximumDiagonalAttribute->getMetricFamily()->willReturn('Length');
        $attributeRepository->findOneByIdentifier('maximum_diagonal')->willReturn($maximumDiagonalAttribute);
        $measureManager->unitCodeExistsInFamily('unknown_unit_code', 'Length')->willReturn(false);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'conversionUnits',
                'unitCode',
                'the metric unit code for the conversion unit does not exist',
                'Pim\Component\Catalog\Updater\ChannelUpdater',
                'unknown_unit_code'
            )
        )->during('update', [$channel, $values]);
    }
}
