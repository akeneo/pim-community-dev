<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PhpSpec\ObjectBehavior;

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
                'attributeCode-EUR-GPB-channelCode-localeCode',
                'attributeCode-USD-channelCode-localeCode',
                'attributeCode-EUR-USD-channelCode-localeCode',
                'attributeCode-GPB-USD-channelCode-localeCode',
                'attributeCode-EUR-GPB-USD-channelCode-localeCode',
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
}
