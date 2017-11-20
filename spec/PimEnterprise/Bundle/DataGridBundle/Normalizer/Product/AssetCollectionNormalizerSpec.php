<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Normalizer\Product;

use Akeneo\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;

class AssetCollectionNormalizerSpec extends ObjectBehavior
{
    function it_should_be_an_asset_collection_normalizer()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\Normalizer\Product\AssetCollectionNormalizer');
    }

    function it_supports_asset_collection(ReferenceDataCollectionValue $assetCollection, AttributeInterface $attribute)
    {
        $assetCollection->getAttribute()->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('assets');

        $this->supportsNormalization($assetCollection, 'datagrid')->shouldReturn(true);
    }

    function it_does_not_support_anything_else(\stdClass $anything)
    {
        $this->supportsNormalization($anything, 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_media(
        ReferenceDataCollectionValue $assetCollection,
        Asset $asset,
        ReferenceInterface $reference,
        FileInfo $fileInfo
    ) {
        $assetCollection->getData()->willReturn([$asset]);
        $asset->getReference()->willReturn($reference);
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileInfo->getOriginalFilename()->willReturn('fileOriginalFilename');
        $fileInfo->getKey()->willReturn('fileKey');

        $this->normalize($assetCollection, 'datagrid', [])->shouldReturn([
            'data' => [
                'originalFilename' => 'fileOriginalFilename',
                'filePath'         => 'fileKey',
            ]
        ]);
    }
}
