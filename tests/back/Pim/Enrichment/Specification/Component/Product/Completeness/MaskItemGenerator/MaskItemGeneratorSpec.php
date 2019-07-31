<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGenerator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MaskItemGeneratorSpec extends ObjectBehavior
{
    public function let(
        MaskItemGeneratorForAttributeType $generator1,
        MaskItemGeneratorForAttributeType $generator2,
        MaskItemGeneratorForAttributeType $generator3
    ) {
        $generator1->supportedAttributeTypes()->willReturn([]);
        $generator2->supportedAttributeTypes()->willReturn(['attributeType2']);
        $generator3->supportedAttributeTypes()->willReturn(['attributeType3', 'attributeType3bis']);
        $this->beConstructedWith([$generator1, $generator2, $generator3]);
    }

    public function it_is_a_mask_item_generator()
    {
        $this->shouldBeAnInstanceOf(MaskItemGenerator::class);
    }

    public function it_returns_existing_generator(
        MaskItemGeneratorForAttributeType $generator1,
        MaskItemGeneratorForAttributeType $generator2,
        MaskItemGeneratorForAttributeType $generator3
    ) {
        $generator1->forRawValue(Argument::cetera())->shouldNotBeCalled();
        $generator2->forRawValue('attributeCode2', 'channelCode', 'localeCode', 'value')->shouldBeCalled()->willReturn(['mask']);
        $generator3->forRawValue(Argument::cetera())->shouldNotBeCalled();
        $this->generate('attributeCode2', 'attributeType2', 'channelCode', 'localeCode', 'value')->shouldReturn(['mask']);
    }

    public function it_should_throw_exception_on_non_existing_generator()
    {
        $this->shouldThrow(new \LogicException('MaskItemGenerator for attribute type "nonExistingAttributeType" not found'))
            ->during('generate', ['attributeCode', 'nonExistingAttributeType', 'channelCode', 'localeCode', 'value']);
    }
}
