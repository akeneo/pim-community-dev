<?php

namespace spec\PimEnterprise\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Api\Normalizer\PublishedProductNormalizer;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $publishedProductNormalizer)
    {
        $this->beConstructedWith($publishedProductNormalizer);
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

    function it_normalizes_a_published_product(PublishedProductInterface $publishedProduct, $publishedProductNormalizer)
    {
        $publishedProductNormalizer->normalize($publishedProduct, 'external_api', [])->willReturn([
            'identifier' => 'foo',
            'categories' => ['bar'],
        ]);

        $this->normalize($publishedProduct, 'external_api')->shouldReturn([
            'identifier' => 'foo',
            'categories' => ['bar']
        ]);
    }
}
