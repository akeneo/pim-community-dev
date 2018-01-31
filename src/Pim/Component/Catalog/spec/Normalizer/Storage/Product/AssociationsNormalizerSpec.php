<?php

namespace spec\Pim\Component\Catalog\Normalizer\Storage\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Storage\Product\AssociationsNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationsNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationsNormalizer::class);
    }

    function it_support_products(ProductInterface $product)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'storage')->shouldReturn(false);
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, 'storage')->shouldReturn(true);
    }

    function it_normalizes_product_assocations($stdNormalizer, ProductInterface $product)
    {
        $stdNormalizer->normalize($product, 'storage', ['context'])->willReturn('std-associations');

        $this->normalize($product, 'storage', ['context'])->shouldReturn('std-associations');
    }
}
