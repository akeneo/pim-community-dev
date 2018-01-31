<?php

namespace spec\Pim\Component\Catalog\Normalizer\Storage;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Normalizer\Storage\AttributeOptionNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOptionNormalizer::class);
    }

    function it_support_attribute_options(AttributeOptionInterface $option)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($option, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($option, 'storage')->shouldReturn(true);
    }

    function it_normalizes_attribute_options($stdNormalizer, AttributeOptionInterface $option)
    {
        $stdNormalizer->normalize($option, 'storage', ['context'])->willReturn('option');

        $this->normalize($option, 'storage', ['context'])->shouldReturn('option');
    }
}
