<?php

namespace spec\PimEnterprise\Bundle\ApiBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ApiBundle\Normalizer\AssetReferenceNormalizer;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetReferenceNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $componentNormalizer, RouterInterface $router)
    {
        $this->beConstructedWith($componentNormalizer, $router);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetReferenceNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_non_localizable_asset_reference(
        $componentNormalizer,
        $router,
        ReferenceInterface $reference,
        AssetInterface $asset
    ) {
        $componentNormalizer->normalize($reference, 'external_api', [])->willReturn([
            'locale' => null,
            'code' => 'path/to/the/file.extension',
        ]);

        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');

        $router->generate(
            'pimee_api_asset_reference_download',
            [
                'code' => 'the_reference_asset',
                'localeCode' => 'no_locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/the_reference_asset/reference-files/no_locale/download');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/the_reference_asset/reference-files/no_locale/download',
                ],
            ],
            'locale' => null,
            'code' => 'path/to/the/file.extension',
        ]);
    }

    function it_normalizes_a_localizable_asset_reference(
        $componentNormalizer,
        $router,
        ReferenceInterface $reference,
        AssetInterface $asset
    ) {
        $componentNormalizer->normalize($reference, 'external_api', [])->willReturn([
            'locale' => 'a_locale',
            'code' => 'path/to/the/file.extension',
        ]);

        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');

        $router->generate(
            'pimee_api_asset_reference_download',
            [
                'code' => 'the_reference_asset',
                'localeCode' => 'a_locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/the_reference_asset/reference-files/a_locale/download');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/the_reference_asset/reference-files/a_locale/download',
                ],
            ],
            'locale' => 'a_locale',
            'code' => 'path/to/the/file.extension',
        ]);
    }
}
