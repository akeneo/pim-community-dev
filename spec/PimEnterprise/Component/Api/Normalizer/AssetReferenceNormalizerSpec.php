<?php

namespace spec\PimEnterprise\Component\Api\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\Api\Normalizer\AssetReferenceNormalizer;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
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

    function it_normalizes_a_non_localized_asset_reference_without_files(
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn(null);

        $normalizedReference = $this->normalize($reference, 'external_api', []);

        $normalizedReference['_link']->shouldBeAnEmptyObject();
        $normalizedReference['locale']->shouldBe(null);
        $normalizedReference['code']->shouldBe(null);
    }

    function it_normalizes_a_localized_asset_reference_without_files(
        ReferenceInterface $reference,
        LocaleInterface $locale
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getFileInfo()->willReturn(null);

        $normalizedReference = $this->normalize($reference, 'external_api', []);

        $normalizedReference['_link']->shouldBeAnEmptyObject();
        $normalizedReference['locale']->shouldBe('a_locale');
        $normalizedReference['code']->shouldBe(null);
    }

    function it_normalizes_a_non_localized_asset_reference_with_files(
        $router,
        ReferenceInterface $reference,
        FileInfoInterface $fileInfo,
        AssetInterface $asset
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');
        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');

        $router->generate(
            'pim_api_asset_reference_download',
            [
                'assetCode' => 'the_reference_asset',
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

    function it_normalizes_a_localized_asset_reference_with_files(
        $router,
        ReferenceInterface $reference,
        LocaleInterface $locale,
        FileInfoInterface $fileInfo,
        AssetInterface $asset
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');
        $reference->getAsset()->willReturn($asset);
        $asset->getCode()->willReturn('the_reference_asset');

        $router->generate(
            'pim_api_asset_reference_download',
            [
                'assetCode' => 'the_reference_asset',
                'localeCode' => 'a_locale',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('/assets/the_reference_asset/reference-files/no_locale/download');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            '_link' => [
                'download' => [
                    'href' => '/assets/the_reference_asset/reference-files/no_locale/download',
                ],
            ],
            'locale' => 'a_locale',
            'code' => 'path/to/the/file.extension',
        ]);
    }

    public function getMatchers()
    {
        return [
            'beAnEmptyObject' => function ($subject) {
                $encodedSubject = json_encode($subject);

                return $subject instanceof \stdClass && $encodedSubject === '{}';
            },
        ];
    }
}
