<?php

namespace spec\PimEnterprise\Component\Security\Normalizer\Authorization;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use PimEnterprise\Component\Security\Normalizer\Authorization\ProductNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_products_in_authorization_format(ProductInterface $product, VariantProductInterface $variantProduct)
    {
        $this->supportsNormalization('string', 'authorization')->shouldReturn(false);
        $this->supportsNormalization($product, 'other')->shouldReturn(false);
        $this->supportsNormalization($product, 'authorization')->shouldReturn(true);
        $this->supportsNormalization($variantProduct, 'authorization')->shouldReturn(true);
    }

    function it_normalizes_a_product(ProductInterface $product)
    {
        $product->getIdentifier()->willReturn('my_sku');
        $product->getCategoryCodes()->willReturn(['cat', 'kitten']);

        $this->normalize($product, 'authorization', [])->shouldReturn([
            'identifier' => 'my_sku',
            'categories' => ['cat', 'kitten']
        ]);
    }

    function it_normalizes_a_variant_product(VariantProductInterface $variantProduct)
    {
        $variantProduct->getIdentifier()->willReturn('my_sku');
        $variantProduct->getCategoryCodes()->willReturn(['cat', 'kitten', 'pet']);

        $this->normalize($variantProduct, 'authorization', [])->shouldReturn([
            'identifier' => 'my_sku',
            'categories' => ['cat', 'kitten', 'pet']
        ]);
    }
}
