<?php

namespace Specification\Akeneo\Channel\Component\Updater;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\ChannelTranslationInterface;
use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Updater\ChannelUpdater;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ChannelUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $currencyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $localeRepository,
            $currencyRepository,
            $attributeRepository,
            $translatableUpdater
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_channel()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                ChannelInterface::class
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
        $translatableUpdater,
        ChannelInterface $channel,
        CategoryInterface $tree,
        LocaleInterface $enUS,
        LocaleInterface $frFR,
        CurrencyInterface $usd,
        CurrencyInterface $eur,
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

        $maximumDiagonalAttribute->getMetricFamily()->willReturn('Length');
        $weightAttribute->getMetricFamily()->willReturn('Weight');

        $attributeRepository->findOneByIdentifier('maximum_diagonal')->willReturn($maximumDiagonalAttribute);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weightAttribute);

        $translatableUpdater->update($channel, $values['labels'])->shouldBeCalled();

        $channel->setConversionUnits([
            'maximum_diagonal' => 'INCHES',
            'weight'           => 'KILOGRAM',
        ])->shouldBeCalled();

        $this->update($channel, $values, []);
    }


    function it_updates_a_channel_with_conversion_units_empty(
        $categoryRepository,
        $localeRepository,
        $currencyRepository,
        $attributeRepository,
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
            'conversion_units' => [],
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

        $channel->getTranslation()->willReturn($channelTranslation);

        $maximumDiagonalAttribute->getMetricFamily()->willReturn('Length');
        $weightAttribute->getMetricFamily()->willReturn('Weight');

        $attributeRepository->findOneByIdentifier('maximum_diagonal')->willReturn($maximumDiagonalAttribute);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weightAttribute);

        $channelTranslation->setLabel('Tablet');
        $channelTranslation->setLabel('Tablette');
        $channel->setConversionUnits([])->shouldBeCalled();

        $this->update($channel, $values, []);
    }

    function it_throws_an_exception_if_category_not_found(
        $categoryRepository,
        ChannelInterface $channel
    ) {
        $values = [
            'category_tree' => 'unknown',
        ];
        $categoryRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'category_tree',
                'code',
                'The category does not exist',
                ChannelUpdater::class,
                'unknown'
            )
        )->during(
            'update',
            [$channel, $values]
        );
    }

    function it_throws_an_exception_if_locale_not_found(
        $localeRepository,
        ChannelInterface $channel
    ) {
        $values = [
            'locales'       => ['unknown'],
        ];
        $localeRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'locales',
                'code',
                'The locale does not exist',
                ChannelUpdater::class,
                'unknown'
            )
        )->during(
            'update',
            [$channel, $values]
        );
    }

    function it_throws_an_exception_if_currency_not_found(
        $currencyRepository,
        ChannelInterface $channel
    ) {
        $values = [
            'currencies'    => ['unknown'],
        ];
        $currencyRepository->findOneByIdentifier('unknown')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'currencies',
                'code',
                'The currency does not exist',
                ChannelUpdater::class,
                'unknown'
            )
        )->during('update', [$channel, $values]);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(ChannelInterface $channel)
    {
        $data = [
            'labels' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'labels',
                    ChannelUpdater::class,
                    'foo'
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_labels_array_is_not_a_scalar(ChannelInterface $channel)
    {
        $data = [
            'labels' => [
                'en_US' => 'us_Label',
                'fr_FR' => [],
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'labels',
                    'one of the "labels" values is not a scalar',
                    ChannelUpdater::class,
                    ['en_US' => 'us_Label', 'fr_FR' => []]
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_locales_is_not_an_array(ChannelInterface $channel)
    {
        $data = [
            'locales' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'locales',
                    ChannelUpdater::class,
                    'foo'
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_locales_array_is_not_a_scalar(ChannelInterface $channel)
    {
        $data = [
            'locales' => ['en_US', []],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'locales',
                    'one of the "locales" values is not a scalar',
                    ChannelUpdater::class,
                    ['en_US', []]
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_currencies_is_not_an_array(ChannelInterface $channel)
    {
        $data = [
            'currencies' => 'EUR',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'currencies',
                    ChannelUpdater::class,
                    'EUR'
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_currencies_array_is_not_a_scalar(ChannelInterface $channel)
    {
        $data = [
            'currencies' => ['EUR', []],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'currencies',
                    'one of the "currencies" values is not a scalar',
                    ChannelUpdater::class,
                    ['EUR', []]
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_conversion_units_is_not_an_array(ChannelInterface $channel)
    {
        $data = [
            'conversion_units' => 'GRAM',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'conversion_units',
                    ChannelUpdater::class,
                    'GRAM'
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_conversion_units_array_is_not_a_scalar(ChannelInterface $channel)
    {
        $data = [
            'conversion_units' => ["weight" => "GRAM", []],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'conversion_units',
                    'one of the "conversion_units" values is not a scalar',
                    ChannelUpdater::class,
                    ["weight" => "GRAM", []]
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_code_is_not_a_scalar(ChannelInterface $channel)
    {
        $data = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'code',
                    ChannelUpdater::class,
                    []
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_category_tree_is_not_a_scalar(ChannelInterface $channel)
    {
        $data = [
            'category_tree' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'category_tree',
                    ChannelUpdater::class,
                    []
                )
            )
            ->during('update', [$channel, $data, []]);
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(ChannelInterface $channel)
    {
        $data = [
            'unknown_field' => 'field',
        ];

        $this->shouldThrow(
            UnknownPropertyException::unknownProperty(
                'unknown_field',
                new NoSuchPropertyException()
            )
        )->during('update', [$channel, $data, []]);
    }
}
