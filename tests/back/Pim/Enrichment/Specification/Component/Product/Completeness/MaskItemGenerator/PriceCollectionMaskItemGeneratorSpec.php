<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PhpSpec\ObjectBehavior;

class PriceCollectionMaskItemGeneratorSpec extends ObjectBehavior
{
    public function let(FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $findActivatedCurrencies->forChannel('channelCode')->willReturn(['USD', 'EUR', 'GPB']);
        $findActivatedCurrencies->forAllChannelsIndexedByChannelCode()->willReturn([
            'channelCode' => ['USD', 'EUR', 'GPB'],
            'otherChannelCode' => ['USD', 'EUR', 'GPB', 'AZN', 'AND', 'BRL', 'CAD', 'CNY', 'NZD', 'CZK', 'DOP', 'FJD', 'GEL', 'GTQ', 'HUF', 'INR', 'JMD', 'LAK', 'CHF', 'MRU', 'MAD'],
        ]);

        $this->beConstructedWith($findActivatedCurrencies);
    }

    public function it_is_a_mask_item_generator()
    {
        $this->shouldBeAnInstanceOf(MaskItemGeneratorForAttributeType::class);
    }

    public function it_adds_ordered_currencies_to_mask_for_a_scopable_attribute()
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'EUR'],
            ['amount' => 50, 'currency' => 'GPB'],
        ];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([
                'attributeCode-EUR-GPB-USD-channelCode-localeCode',
            ]);
    }

    public function it_adds_ordered_currencies_to_mask_for_a_non_scopable_attribute()
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 3000, 'currency' => 'DOP'],
            ['amount' => 50, 'currency' => 'GPB'],
        ];
        $this->forRawValue('attributeCode', '<all_channels>', 'localeCode', $value)
             ->shouldReturn([
                 'attributeCode-GPB-USD-<all_channels>-localeCode',
                 'attributeCode-DOP-GPB-USD-<all_channels>-localeCode',
             ]);
    }

    public function it_filters_empty_prices()
    {
        $value = [
            ['amount' => null, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'EUR']
        ];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([
                'attributeCode-EUR-channelCode-localeCode',
            ]);
    }

    public function it_filters_non_active_currencies_for_channel()
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'CNY'],
            ['amount' => 50, 'currency' => 'GPB'],
        ];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([
                'attributeCode-GPB-USD-channelCode-localeCode',
            ]);
    }

    public function it_filters_non_existing_channel(FindActivatedCurrenciesInterface $findActivatedCurrencies)
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'EUR'],
            ['amount' => 50, 'currency' => 'GPB'],
        ];
        $this->forRawValue('attributeCode', 'nonExistingChannel', 'localeCode', $value)
            ->shouldReturn([]);
    }

    public function it_adds_ordered_currencies_to_mask_with_a_lot_of_currencies()
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'EUR'],
            ['amount' => 50, 'currency' => 'GPB'],
            ['amount' => 50, 'currency' => 'AZN'],
            ['amount' => 50, 'currency' => 'AND'],
            ['amount' => 50, 'currency' => 'CAD'],
            ['amount' => 50, 'currency' => 'CNY'],
            ['amount' => 50, 'currency' => 'NZD'],
            ['amount' => 50, 'currency' => 'DOP'],
            ['amount' => 50, 'currency' => 'FJD'],
            ['amount' => 50, 'currency' => 'GEL'],
            ['amount' => 50, 'currency' => 'GTQ'],
            ['amount' => 50, 'currency' => 'HUF'],
            ['amount' => 50, 'currency' => 'INR'],
            ['amount' => 50, 'currency' => 'JMD'],
            ['amount' => 50, 'currency' => 'LAK'],
            ['amount' => 50, 'currency' => 'CHF'],
            ['amount' => 50, 'currency' => 'MRU'],
            ['amount' => 50, 'currency' => 'MAD'],
            ['amount' => 60, 'currency' => 'UNKNOWN'],
        ];

        $this->forRawValue('attributeCode', '<all_channels>', 'localeCode', $value)
             ->shouldReturn([
                 'attributeCode-EUR-GPB-USD-<all_channels>-localeCode',
                 'attributeCode-AND-AZN-CAD-CHF-CNY-DOP-EUR-FJD-GEL-GPB-GTQ-HUF-INR-JMD-LAK-MAD-MRU-NZD-USD-<all_channels>-localeCode',
             ]);

    }
}
