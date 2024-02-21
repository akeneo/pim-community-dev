<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PhpSpec\ObjectBehavior;

class MetricMaskItemGeneratorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith();
    }

    public function it_is_a_mask_item_generator()
    {
        $this->shouldBeAnInstanceOf(MaskItemGeneratorForAttributeType::class);
    }

    public function it_adds_mask_on_filled_metric()
    {
        $value = ['amount' => 200, 'unit' => 'UNIT', 'base_data' => 0.2, 'base_unit' => 'BASEUNIT'];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn(['attributeCode-channelCode-localeCode']);
    }

    public function it_does_not_add_mask_when_missing_unit()
    {
        $value = ['amount' => 200, 'base_data' => 0.2, 'base_unit' => 'BASEUNIT'];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([]);
    }

    public function it_does_not_add_mask_when_missing_amount()
    {
        $value = ['unit' => 'UNIT', 'base_data' => 0.2, 'base_unit' => 'BASEUNIT'];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([]);
    }

    public function it_does_not_add_mask_when_missing_base_data()
    {
        $value = ['amount' => 200, 'unit' => 'UNIT', 'base_unit' => 'BASEUNIT'];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([]);
    }

    public function it_does_not_add_mask_when_missing_base_unit()
    {
        $value = ['amount' => 200, 'unit' => 'UNIT', 'base_data' => 0.2];
        $this->forRawValue('attributeCode', 'channelCode', 'localeCode', $value)
            ->shouldReturn([]);
    }
}
