<?php

namespace spec\Pim\Component\Api\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\ProductModelNormalizer;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
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
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_supports_a_product(ProductModelInterface $productModel)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_product_model_to_the_api_format(
        $stdNormalizer,
        $attributeRepository,
        $router,
        ProductModelInterface $productModel
    ) {
        $productModelStandard = [
            'identifier' => 'foo',
            'values'     => [
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
            ]
        ];

        $productModelApiFormat = [
            'identifier' => 'foo',
            'values'     => [
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
                        'data'   => 'a/b/c/artyui_file.txt',
                        '_links' => [
                            'download' => [
                                'href' => 'http://localhost/api/rest/v1/a/b/c/artyui_file.txt/download'
                            ]
                        ],
                    ]
                ]
            ]
        ];

        $stdNormalizer->normalize($productModel, 'standard', [])->willReturn($productModelStandard);

        $attributeRepository->getMediaAttributeCodes()->willReturn(['file']);
        $router->generate('pim_api_media_file_download', ['code' => 'a/b/c/artyui_file.txt'], Argument::any())
            ->willReturn('http://localhost/api/rest/v1/a/b/c/artyui_file.txt/download');

        $this->normalize($productModel, 'external_api', [])->shouldReturn($productModelApiFormat);
    }

    function it_normalizes_empty_values_as_an_object_for_json_serialization(
        $stdNormalizer,
        $attributeRepository,
        ProductModelInterface $productModel
    ) {
        $productStandard = [
            'identifier'   => 'foo',
            'values'       => [],
            'associations' => []
        ];

        $stdNormalizer->normalize($productModel, 'standard', [])->willReturn($productStandard);

        $attributeRepository->getMediaAttributeCodes()->willReturn(['file']);
        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $normalizedProduct = $this->normalize($productModel, 'external_api', []);
        $normalizedProduct->shouldHaveValues($productStandard);
    }

    public function getMatchers()
    {
        return [
            'haveValues' => function ($subject) {
                return is_object($subject['values']);
            }
        ];
    }
}
