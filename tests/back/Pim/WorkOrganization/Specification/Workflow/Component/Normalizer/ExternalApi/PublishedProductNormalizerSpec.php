<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi\PublishedProductNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $publishedProductNormalizer, NormalizerInterface $associationNormalizer)
    {
        $this->beConstructedWith($publishedProductNormalizer, $associationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PublishedProductNormalizer::class);
    }

    function it_should_implement()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_published_product_normalization(
        $publishedProductNormalizer,
        PublishedProductInterface $publishedProduct
    ) {
        $publishedProductNormalizer
            ->supportsNormalization($publishedProduct, 'external_api')
            ->willReturn(true);

        $this->supportsNormalization($publishedProduct, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_published_product(
        PublishedProductInterface $publishedProduct,
        $publishedProductNormalizer,
        NormalizerInterface $associationNormalizer
    ) {
        $publishedProductNormalizer->normalize($publishedProduct, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $associationNormalizer->normalize($publishedProduct, 'external_api', [])->shouldBeCalledOnce()->willReturn([]);

        $this->normalize($publishedProduct, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'associations' => []
        ]);
    }

    function it_normalizes_a_published_product_with_quality_scores(
        PublishedProductInterface $publishedProduct,
        $publishedProductNormalizer,
        NormalizerInterface $associationNormalizer
    ) {
        $publishedProductNormalizer->normalize($publishedProduct, 'external_api', ['with_quality_scores' => true])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $associationNormalizer
            ->normalize($publishedProduct, 'external_api', ['with_quality_scores' => true])
            ->shouldBeCalledOnce()
            ->willReturn([]);

        $this->normalize($publishedProduct, 'external_api', ['with_quality_scores' => true])->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'quality_scores' => [],
            'associations' => []
        ]);
    }

    function it_normalizes_a_published_product_with_associations(
        PublishedProductInterface $publishedProduct,
        $publishedProductNormalizer,
        NormalizerInterface $associationNormalizer
    ) {
        $publishedProductNormalizer->normalize($publishedProduct, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $associationNormalizer
            ->normalize($publishedProduct, 'external_api', [])
            ->shouldBeCalledOnce()
            ->willReturn([
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [
                        '1111111171'
                    ],
                    'product_models' => []
                ],
            ]);

        $this->normalize($publishedProduct, 'external_api', [])->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
            'associations' => [
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [
                        '1111111171'
                    ],
                    'product_models' => []
                ],
            ],
        ]);
    }
}
