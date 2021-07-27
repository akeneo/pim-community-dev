<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Normalizer\Standard\AttributeNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $baseNormalizer)
    {
        $baseNormalizer->supportsNormalization(Argument::type(AttributeInterface::class), 'standard')->willReturn(true);
        $baseNormalizer->supportsNormalization(Argument::cetera())->willReturn(false);
        $this->beConstructedWith($baseNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_only_normalizes_attributes_in_standard_format(AttributeInterface $attribute)
    {
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization($attribute, 'flat')->shouldBe(false);
        $this->supportsNormalization($attribute, 'standard')->shouldBe(true);
    }

    function it_normalizes_a_non_table_attribute_to_standard_format(
        NormalizerInterface $baseNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_text');
        $baseNormalizer->normalize($attribute, 'standard', [])->shouldBeCalled()->willReturn(['code' => 'foo']);
        $this->normalize($attribute, 'standard', [])->shouldReturn(['code' => 'foo']);
    }

    function it_normalizes_a_table_attribute_to_standard_format(
        NormalizerInterface $baseNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_table');
        $attribute->getRawTableConfiguration()->willReturn([
            ['data_type' => 'select', 'code' => 'ingredient'],
            ['data_type' => 'number', 'code' => 'quantity'],
        ]);
        $baseNormalizer->normalize($attribute, 'standard', [])->shouldBeCalled()->willReturn(['code' => 'nutrition']);
        $this->normalize($attribute, 'standard', [])->shouldReturn([
            'code' => 'nutrition',
            'table_configuration' => [
                ['data_type' => 'select', 'code' => 'ingredient'],
                ['data_type' => 'number', 'code' => 'quantity'],
            ],
        ]);
    }
}
