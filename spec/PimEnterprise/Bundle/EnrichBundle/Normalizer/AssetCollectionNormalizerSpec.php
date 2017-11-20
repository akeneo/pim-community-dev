<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

class AssetCollectionNormalizerSpec extends ObjectBehavior
{
    function let(FileNormalizer $fileNormalizer)
    {
        $this->beConstructedWith($fileNormalizer);
    }

    function it_should_be_an_asset_collection_normalizer()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\Normalizer\AssetCollectionNormalizer');
    }

    function it_supports_asset_collection(ReferenceDataCollectionValue $assetCollection, Asset $asset)
    {
        $assetCollection->getData()->willReturn([$asset]);

        $this->supportsNormalization($assetCollection, 'internal_api')->shouldReturn(true);
    }

    function it_does_not_support_anything_else(\stdClass $anything)
    {
        $this->supportsNormalization($anything, 'internal_api')->shouldReturn(false);
    }

    function it_normalizes_media(
        FileNormalizer $fileNormalizer,
        ReferenceDataCollectionValue $assetCollection,
        Asset $asset,
        ReferenceInterface $reference,
        FileInfo $fileInfo
    ) {
        $assetCollection->getData()->willReturn([$asset]);
        $asset->getReference()->willReturn($reference);
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileNormalizer->normalize($fileInfo, 'internal_api', [])->willReturn([
            'filePath'         => 'fileKey',
            'originalFilename' => 'fileOriginalFilename',
        ]);

        $this->normalize($assetCollection, 'internal_api', [])->shouldReturn([
            'filePath'         => 'fileKey',
            'originalFilename' => 'fileOriginalFilename',
        ]);
    }
}
