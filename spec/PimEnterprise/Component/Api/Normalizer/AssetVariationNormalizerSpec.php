<?php

namespace spec\PimEnterprise\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Api\Normalizer\AssetVariationNormalizer;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetVariationNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer)
    {
        $this->beConstructedWith($standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetVariationNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_reference_api_normalization(VariationInterface $variation)
    {
        $this->supportsNormalization($variation, 'external_api')->shouldReturn(true);
        $this->supportsNormalization($variation, 'foobar')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
    }

    function it_normalizes_an_asset_variation(
        $standardNormalizer,
        VariationInterface $variation
    ) {
        $standardNormalizer->normalize($variation, 'external_api', [])->willReturn([
            'code' => 'path/to/variation_file.jpg',
            'asset' => 'an_asset',
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'reference_file' => 'path/to/reference_file.jpg',
        ]);

        $this->normalize($variation, 'external_api', [])->shouldReturn([
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);
    }
}
