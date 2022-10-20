<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class PriceCollectionMaskItemGeneratorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith();
    }

    public function it_is_a_mask_item_generator()
    {
        $this->shouldBeAnInstanceOf(MaskItemGeneratorForAttributeType::class);
    }

    public function it_adds_ordered_currencies_to_mask()
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'EUR'],
            ['amount' => 50, 'currency' => 'GPB'],
        ];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([
                'attributeCode-EUR-channelCode-localeCode',
                'attributeCode-GPB-channelCode-localeCode',
                'attributeCode-USD-channelCode-localeCode',
                'attributeCode-EUR-GPB-channelCode-localeCode',
                'attributeCode-EUR-USD-channelCode-localeCode',
                'attributeCode-EUR-GPB-USD-channelCode-localeCode',
                'attributeCode-GPB-USD-channelCode-localeCode',
            ]);
    }

    public function it_does_not_add_null_amount()
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

    public function it_adds_ordered_currencies_to_mask_with_a_lot_of_currencies()
    {
        $value = [
            ['amount' => 200, 'currency' => 'USD'],
            ['amount' => 100, 'currency' => 'EUR'],
            ['amount' => 50, 'currency' => 'GPB'],
            ['amount' => 50, 'currency' => 'AZN'],
            ['amount' => 50, 'currency' => 'AND'],
            ['amount' => 50, 'currency' => 'BRL'],
            ['amount' => 50, 'currency' => 'CAD'],
            ['amount' => 50, 'currency' => 'CNY'],
            ['amount' => 50, 'currency' => 'NZD'],
            ['amount' => 50, 'currency' => 'CZK'],
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
        ];

        Assert::assertCount(
            pow(2, (int) (count($value))) - 1,
            $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)->getWrappedObject()
        );
    }
}
