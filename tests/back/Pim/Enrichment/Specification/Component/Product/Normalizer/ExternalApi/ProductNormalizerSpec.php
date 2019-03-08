<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ProductNormalizer;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
            'associations' => [
                'X_SELL' => [
                    'groups' => ['bar'],
                    'products' => ['foo']
                ]
            ]
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

        $attributeRepository->getMediaAttributeCodes()->willReturn(['file']);
        $router->generate('pim_api_media_file_download', ['code' => 'a/b/c/artyui_file.txt'], Argument::any())
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
            'associations' => [
                'UPSELL' => [
                    'groups' => ['foo'],
                    'products' => ['bar']
                ]
            ]
        ];

        $context = ['attributes' => ['number']];
        $stdNormalizer->normalize($product, 'standard', $context)->willReturn($productStandard);

        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $productExternal = $productStandard;
        unset($productExternal['values']['sku']);
        unset($productExternal['values']['text']);
        unset($productExternal['values']['file']);

        $attributeRepository->getMediaAttributeCodes()->willReturn(['file']);
        $router->generate(Argument::any(), ['code' => 'a/b/c/artyui_file.txt'], Argument::any())->shouldNotBeCalled();

        $this->normalize($product, 'external_api', $context)->shouldReturn($productExternal);
    }

    function it_normalizes_empty_values_and_associations(
        $stdNormalizer,
        $attributeRepository,
        ProductInterface $product
    ) {
        $productStandard = [
            'identifier'   => 'foo',
            'values'       => [],
            'associations' => []
        ];

        $stdNormalizer->normalize($product, 'standard', [])->willReturn($productStandard);

        $attributeRepository->getMediaAttributeCodes()->willReturn(['file']);
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $normalizedProduct = $this->normalize($product, 'external_api', []);
        $normalizedProduct->shouldHaveValues($productStandard);
        $normalizedProduct->shouldHaveAssociations($productStandard);
    }

    public function getMatchers(): array
    {
        return [
            'haveAssociations' => function ($subject) {
                return is_object($subject['associations']);
            },
            'haveValues' => function ($subject) {
                return is_object($subject['values']);
            }
        ];
    }
}
