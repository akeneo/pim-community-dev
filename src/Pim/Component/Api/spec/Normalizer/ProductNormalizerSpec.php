<?php

namespace spec\Pim\Component\Api\Normalizer;

use Pim\Component\Api\Normalizer\ProductNormalizer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $stdNormalizer,
        AttributeRepositoryInterface $attributeRepository,
        RouterInterface $router
    ) {
        $this->beConstructedWith($stdNormalizer, $attributeRepository, $router);
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

    function it_normalizes_a_product($stdNormalizer, $attributeRepository, $router, ProductInterface $product)
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
                ],
                'file' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'a/b/c/artyui_file.txt'
                    ]
                ]
            ],
        ];

        $stdNormalizer->normalize($product, 'standard', [])->willReturn($productStandard);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $productExternal = $productStandard;
        unset($productExternal['values']['sku']);
        $productExternal['values']['file'][0]['_links'] = [
            'download' => [
                'href' => 'http://localhost/api/rest/v1/a/b/c/artyui_file.txt/download'
            ]
        ];

        $attributeRepository->getAttributeTypeByCodes(['text', 'file'])
            ->willReturn(['text' => 'pim_catalog_text', 'file' => 'pim_catalog_file']);
        $router->generate(Argument::any(), ['code' => 'a/b/c/artyui_file.txt'], Argument::any())
            ->willReturn('http://localhost/api/rest/v1/a/b/c/artyui_file.txt/download');

        $this->normalize($product, 'external_api', [])->shouldReturn($productExternal);
    }

    function it_normalizes_a_product_with_a_list_of_attributes(
        $stdNormalizer,
        $attributeRepository,
        $router,
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
                ],
                'file' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'a/b/c/artyui_file.txt'
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
        unset($productExternal['values']['file']);

        $attributeRepository->getAttributeTypeByCodes(['text', 'number', 'file'])
            ->willReturn(['text' => 'pim_catalog_text', 'number' => 'pim_catalog_number', 'file' => 'pim_catalog_file']);
        $router->generate(Argument::any(), ['code' => 'a/b/c/artyui_file.txt'], Argument::any())->shouldNotBeCalled();

        $this->normalize($product, 'external_api', $context)->shouldReturn($productExternal);
    }
}
