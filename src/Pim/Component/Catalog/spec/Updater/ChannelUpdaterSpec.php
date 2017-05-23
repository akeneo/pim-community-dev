<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Component\Localization\TranslatableUpdater;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Updater\ChannelUpdater;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ChannelUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $currencyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureManager $measureManager,
        TranslatableUpdater $translatableUpdater
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $localeRepository,
            $currencyRepository,
            $attributeRepository,
            $measureManager,
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
        $measureManager,
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

        $measureManager->unitCodeExistsInFamily('INCHES', 'Length')->willReturn(true);
        $measureManager->unitCodeExistsInFamily('KILOGRAM', 'Weight')->willReturn(true);

        $translatableUpdater->update($channel, $values['labels'])->shouldBeCalled();

        $channel->setConversionUnits([
            'maximum_diagonal' => 'INCHES',
            'weight'           => 'KILOGRAM',
        ])->shouldBeCalled();

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

    function it_throws_an_exception_if_conversion_unit_attribute_does_not_exist(
        $attributeRepository,
        ChannelInterface $channel
    ) {
        $values = [
            'conversion_units' => ['unknown_attribute' => 'INCHES'],
        ];
        $attributeRepository->findOneByIdentifier('unknown_attribute')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'conversionUnits',
                'attributeCode',
                'the attribute code for the conversion unit does not exist',
                ChannelUpdater::class,
                'unknown_attribute'
            )
        )->during('update', [$channel, $values]);
    }

    function it_throws_an_exception_if_conversion_unit_metric_code_does_not_exist(
        $attributeRepository,
        $measureManager,
        ChannelInterface $channel,
        AttributeInterface $maximumDiagonalAttribute
    ) {
        $values = [
            'conversion_units' => ['maximum_diagonal' => 'unknown_unit_code'],
        ];

        $maximumDiagonalAttribute->getMetricFamily()->willReturn('Length');
        $attributeRepository->findOneByIdentifier('maximum_diagonal')->willReturn($maximumDiagonalAttribute);
        $measureManager->unitCodeExistsInFamily('unknown_unit_code', 'Length')->willReturn(false);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'conversionUnits',
                'unitCode',
                'the metric unit code for the conversion unit does not exist',
                ChannelUpdater::class,
                'unknown_unit_code'
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
