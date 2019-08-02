<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $propertiesNormalizer, NormalizerInterface $associationsNormalizer)
    {
        $this->beConstructedWith($propertiesNormalizer, $associationsNormalizer);
    }

    function it_supports_published_product_interface_in_standard_format(PublishedProductInterface $publishedProduct)
    {
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($publishedProduct, 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($publishedProduct, 'standard')->shouldReturn(true);
    }

    function it_normalizes_a_variant_published_product_interface(
        $propertiesNormalizer,
        $associationsNormalizer,
        PublishedProductInterface $publishedProduct,
        ProductInterface $originalProduct,
        ProductModelInterface $productModel
    ) {
        $propertiesNormalizer->normalize($publishedProduct, 'standard', [])->willReturn(
            [
                'identifier' => 'my_identifier',
                'label' => 'My product',
                'family' => 'familyA',
                'parent' => null,
                'groups' => [],
                'categories' => [],
                'enabled' => true,
                'values' => [],
                'created' => '2019-05-14',
                'updated' => '2019-05-15',
            ]
        );
        $associationsNormalizer->normalize($publishedProduct, 'standard', [])->willReturn(
            [
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ]
        );

        $originalProduct->isVariant()->willReturn(true);
        $originalProduct->getParent()->willReturn($productModel);
        $productModel->getCode()->willReturn('product_model_1');
        $publishedProduct->getOriginalProduct()->willReturn($originalProduct);

        $this->normalize($publishedProduct, 'standard')->shouldReturn(
            [

                'identifier' => 'my_identifier',
                'label' => 'My product',
                'family' => 'familyA',
                'parent' => 'product_model_1',
                'groups' => [],
                'categories' => [],
                'enabled' => true,
                'values' => [],
                'created' => '2019-05-14',
                'updated' => '2019-05-15',
                'associations' => [
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                ],
            ]
        );
    }

    function it_normalizes_a_non_variant_published_product_interface(
        $propertiesNormalizer,
        $associationsNormalizer,
        PublishedProductInterface $publishedProduct,
        ProductInterface $originalProduct
    ) {
        $propertiesNormalizer->normalize($publishedProduct, 'standard', [])->willReturn(
            [
                'identifier' => 'my_identifier',
                'label' => 'My product',
                'family' => 'familyA',
                'parent' => null,
                'groups' => [],
                'categories' => [],
                'enabled' => true,
                'values' => [],
                'created' => '2019-05-14',
                'updated' => '2019-05-15',
            ]
        );
        $associationsNormalizer->normalize($publishedProduct, 'standard', [])->willReturn(
            [
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ]
        );

        $originalProduct->isVariant()->willReturn(false);
        $publishedProduct->getOriginalProduct()->willReturn($originalProduct);

        $this->normalize($publishedProduct, 'standard')->shouldReturn(
            [

                'identifier' => 'my_identifier',
                'label' => 'My product',
                'family' => 'familyA',
                'parent' => null,
                'groups' => [],
                'categories' => [],
                'enabled' => true,
                'values' => [],
                'created' => '2019-05-14',
                'updated' => '2019-05-15',
                'associations' => [
                    'UPSELL' => [
                        'products' => [],
                        'product_models' => [],
                        'groups' => [],
                    ],
                ],
            ]
        );
    }
}
