<?php

namespace spec\PimEnterprise\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Api\Normalizer\AssetVariationNormalizer;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetVariationNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith($standardNormalizer, $router);
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

    function it_normalizes_a_non_localizable_variation(
        $standardNormalizer,
        $router,
        VariationInterface $variation
    ) {
        $standardNormalizer->normalize($variation, 'external_api', [])->willReturn([
            'code' => 'path/to/variation_file.jpg',
            'asset' => 'an_asset',
            'locale' => null,
            'channel' => 'ecommerce',
            'reference_file' => 'path/to/reference_file.jpg',
        ]);

        $router->generate(
            'pim_api_asset_variation_download',
            [
                'code' => 'an_asset',
                'channelCode' => 'ecommerce',
                'localeCode' => 'no_locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/an_asset/variation-files/ecommerce/no_locale/download');

        $this->normalize($variation, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/an_asset/variation-files/ecommerce/no_locale/download',
                ],
            ],
            'locale' => null,
            'channel' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);
    }

    function it_normalizes_a_localizable_variation(
        $standardNormalizer,
        $router,
        VariationInterface $variation
    ) {
        $standardNormalizer->normalize($variation, 'external_api', [])->willReturn([
            'code' => 'path/to/variation_file.jpg',
            'asset' => 'an_asset',
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'reference_file' => 'path/to/reference_file.jpg',
        ]);

        $router->generate(
            'pim_api_asset_variation_download',
            [
                'code' => 'an_asset',
                'channelCode' => 'ecommerce',
                'localeCode' => 'en_US',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/an_asset/variation-files/ecommerce/en_US/download');

        $this->normalize($variation, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/an_asset/variation-files/ecommerce/en_US/download',
                ],
            ],
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);
    }
}
