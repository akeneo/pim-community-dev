<?php

namespace Specification\Akeneo\Asset\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\Normalizer\ExternalApi\AssetVariationNormalizer;
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
        VariationInterface $variation,
        AssetInterface $asset
    ) {
        $standardNormalizer->normalize($variation, 'external_api', [])->willReturn([
            'locale' => null,
            'channel' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);

        $variation->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('an_asset');

        $router->generate(
            'pimee_api_asset_variation_download',
            [
                'code' => 'an_asset',
                'channelCode' => 'ecommerce',
                'localeCode' => 'no-locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/an_asset/variation-files/ecommerce/no-locale/download');

        $this->normalize($variation, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/an_asset/variation-files/ecommerce/no-locale/download',
                ],
            ],
            'locale' => null,
            'scope' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);
    }

    function it_normalizes_a_localizable_variation(
        $standardNormalizer,
        $router,
        VariationInterface $variation,
        AssetInterface $asset
    ) {
        $standardNormalizer->normalize($variation, 'external_api', [])->willReturn([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);

        $variation->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('an_asset');

        $router->generate(
            'pimee_api_asset_variation_download',
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
            'scope' => 'ecommerce',
            'code' => 'path/to/variation_file.jpg',
        ]);
    }
}
