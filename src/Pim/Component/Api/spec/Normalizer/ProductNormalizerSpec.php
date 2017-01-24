<?php

namespace spec\Pim\Component\Api\Normalizer;

use Pim\Component\Api\Normalizer\ProductNormalizer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($stdNormalizer, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_supports_a_product(ProductInterface $product)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($product, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($product, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_product($stdNormalizer, $attributeRepository, ProductInterface $product)
    {
        $productStandard = [
            'identifier' => 'foo',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'foo'
                    ]
                ],
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'text'
                    ]
                ]
            ],
        ];

        $stdNormalizer->normalize($product, 'standard', [])->willReturn($productStandard);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $productExternal = $productStandard;
        unset($productExternal['values']['sku']);

        $this->normalize($product, 'external_api', [])->shouldReturn($productExternal);
    }

    function it_normalizes_a_product_with_a_list_of_attributes(
        $stdNormalizer,
        $attributeRepository,
        ProductInterface $product
    ) {
        $productStandard = [
            'identifier' => 'foo',
            'values'     => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'foo'
                    ]
                ],
                'text' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'text'
                    ]
                ],
                'number' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 12
                    ]
                ]
            ],
        ];

        $context = ['attributes' => ['number']];
        $stdNormalizer->normalize($product, 'standard', $context)->willReturn($productStandard);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $productExternal = $productStandard;
        unset($productExternal['values']['sku']);
        unset($productExternal['values']['text']);

        $this->normalize($product, 'external_api', $context)->shouldReturn($productExternal);
    }
}
