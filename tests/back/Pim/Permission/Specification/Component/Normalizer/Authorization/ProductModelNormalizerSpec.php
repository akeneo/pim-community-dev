<?php

namespace Specification\Akeneo\Pim\Permission\Component\Normalizer\Authorization;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Normalizer\Authorization\ProductModelNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_product_models_in_authorization_format(ProductModelInterface $productModel)
    {
        $this->supportsNormalization('string', 'authorization')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'other')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'authorization')->shouldReturn(true);
    }

    function it_normalizes_a_product(ProductModelInterface $productModel)
    {
        $productModel->getCode()->willReturn('my_sku');
        $productModel->getCategoryCodes()->willReturn(['cat', 'kitten']);

        $this->normalize($productModel, 'authorization', [])->shouldReturn([
            'code' => 'my_sku',
            'categories' => ['cat', 'kitten']
        ]);
    }
}
