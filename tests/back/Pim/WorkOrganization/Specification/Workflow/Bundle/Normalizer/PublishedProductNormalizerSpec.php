<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $productNormalizer)
    {
        $this->beConstructedWith($productNormalizer);
    }

    function it_supports_published_product_interface_in_standard_format(
        \stdClass $object,
        PublishedProductInterface $publishedProduct
    ) {
        $this->supportsNormalization($object, 'standard')->shouldReturn(false);
        $this->supportsNormalization($object, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($publishedProduct, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($publishedProduct, 'standard')->shouldReturn(true);
    }

    function it_normalizes_published_product_interface_in_standard_format_with_a_variant_product(
        $productNormalizer,
        PublishedProductInterface $publishedProduct,
        ProductInterface $product,
        ProductModelInterface $productModel
    ) {
        $product->isVariant()->willReturn(true);
        $product->getParent()->willReturn($productModel);
        $productModel->getCode()->willReturn('product_model_1');

        $publishedProduct->getOriginalProduct()->willReturn($product);
        $productNormalizer->normalize($publishedProduct, 'standard', [])->willReturn([
            'product_data' => []
        ]);

        $this->normalize($publishedProduct, 'standard')->shouldReturn([
            'product_data' => [],
            'parent' => 'product_model_1'
        ]);
    }

    function it_normalizes_published_product_interface_in_standard_format_with_a_normal_product(
        $productNormalizer,
        PublishedProductInterface $publishedProduct,
        ProductInterface $product
    ) {
        $product->isVariant()->willReturn(false);
        $product->getParent()->shouldNotBeCalled();

        $publishedProduct->getOriginalProduct()->willReturn($product);
        $productNormalizer->normalize($publishedProduct, 'standard', [])->willReturn([
            'product_data' => []
        ]);

        $this->normalize($publishedProduct, 'standard')->shouldReturn([
            'product_data' => []
        ]);
    }
}
