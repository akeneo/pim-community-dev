<?php

namespace spec\PimEnterprise\Component\Api\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\Api\Normalizer\AssetReferenceNormalizer;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssetReferenceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetReferenceNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_non_localizable_asset_reference_without_files(
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn(null);

        $normalizedReference = $this->normalize($reference, 'external_api', []);

        $normalizedReference['locale']->shouldBe(null);
        $normalizedReference['code']->shouldBe(null);
    }

    function it_normalizes_a_localizable_asset_reference_without_files(
        ReferenceInterface $reference,
        LocaleInterface $locale
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getFileInfo()->willReturn(null);

        $normalizedReference = $this->normalize($reference, 'external_api', []);

        $normalizedReference['locale']->shouldBe('a_locale');
        $normalizedReference['code']->shouldBe(null);
    }

    function it_normalizes_a_non_localizable_asset_reference_with_files(
        ReferenceInterface $reference,
        FileInfoInterface $fileInfo
    ) {
        $reference->getLocale()->willReturn(null);
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            'locale' => null,
            'code' => 'path/to/the/file.extension',
        ]);
    }

    function it_normalizes_a_localizable_asset_reference_with_files(
        ReferenceInterface $reference,
        LocaleInterface $locale,
        FileInfoInterface $fileInfo
    ) {
        $reference->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('a_locale');
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getKey()->willReturn('path/to/the/file.extension');

        $this->normalize($reference, 'external_api', [])->shouldReturn([
            'locale' => 'a_locale',
            'code' => 'path/to/the/file.extension',
        ]);
    }
}
