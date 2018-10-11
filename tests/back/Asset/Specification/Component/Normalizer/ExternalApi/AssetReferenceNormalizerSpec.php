<?php

namespace Specification\Akeneo\Asset\Component\Normalizer\ExternalApi;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Normalizer\ExternalApi\AssetReferenceNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetReferenceNormalizerSpec extends ObjectBehavior
{
    function let(RouterInterface $router)
    {
        $this->beConstructedWith($router);
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
        $router,
        ReferenceInterface $reference,
        AssetInterface $asset,
        FileInfoInterface $fileInfo
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');

        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');

        $router->generate(
            'pimee_api_asset_reference_download',
            [
                'code' => 'the_reference_asset',
                'localeCode' => 'no-locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/the_reference_asset/reference-files/no-locale/download');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/the_reference_asset/reference-files/no-locale/download',
                ],
            ],
            'locale' => null,
            'code' => 'path/to/the/file.extension',
        ]);
    }

    function it_normalizes_a_localizable_asset_reference(
        $router,
        LocaleInterface $locale,
        ReferenceInterface $reference,
        AssetInterface $asset,
        FileInfoInterface $fileInfo
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');

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

    function it_normalizes_a_non_localizable_asset_reference_without_file(
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn(null);

        $normalizedReference = $this->normalize($reference, 'external_api', []);

        $normalizedReference['locale']->shouldBe(null);
        $normalizedReference['code']->shouldBe(null);
    }

    function it_normalizes_a_localizable_asset_reference_without_file(
        ReferenceInterface $reference,
        AssetInterface $asset,
        LocaleInterface $locale
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getFileInfo()->willReturn(null);
        $reference->getAsset()->willReturn($asset);

        $normalizedReference = $this->normalize($reference, 'external_api', []);

        $normalizedReference['locale']->shouldBe('a_locale');
        $normalizedReference['code']->shouldBe(null);
    }

    function it_normalizes_a_non_localizable_asset_reference_with_file(
        $router,
        ReferenceInterface $reference,
        AssetInterface $asset,
        FileInfoInterface $fileInfo
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn($fileInfo);
        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');

        $router->generate(
            'pimee_api_asset_reference_download',
            [
                'code' => 'the_reference_asset',
                'localeCode' => 'no-locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/the_reference_asset/reference-files/no-locale/download');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/the_reference_asset/reference-files/no-locale/download',
                ],
            ],
            'locale' => null,
            'code' => 'path/to/the/file.extension',
        ]);
    }

    function it_normalizes_a_localizable_asset_reference_with_file(
        $router,
        ReferenceInterface $reference,
        LocaleInterface $locale,
        AssetInterface $asset,
        FileInfoInterface $fileInfo
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');

        $router->generate(
            'pimee_api_asset_reference_download',
            [
                'code' => 'the_reference_asset',
                'localeCode' => 'a_locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/the_reference_asset/reference-files/no-locale/download');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/the_reference_asset/reference-files/no-locale/download',
                ],
            ],
            'locale' => 'a_locale',
            'code' => 'path/to/the/file.extension',
        ]);
    }
}
