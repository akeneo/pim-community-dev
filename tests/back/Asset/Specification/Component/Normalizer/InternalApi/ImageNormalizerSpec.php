<?php

namespace Specification\Akeneo\Asset\Component\Normalizer\InternalApi;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\Reference;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\AssetPreviewGenerator;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ImageNormalizerSpec extends ObjectBehavior
{
    function let(
        FileNormalizer $fileNormalizer,
        LocaleRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        AssetPreviewGenerator $assetPreviewGenerator
    ) {
        $this->beConstructedWith(
            $fileNormalizer,
            $localeRepository,
            $attributeRepository,
            $repositoryResolver,
            $assetPreviewGenerator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImageNormalizer::class);
    }

    function it_normalizes_an_asset(
        $fileNormalizer,
        $localeRepository,
        $attributeRepository,
        $repositoryResolver,
        AssetInterface $asset,
        AttributeInterface $attribute,
        ReferenceInterface $reference,
        FileInfoInterface $fileInfo,
        ReferenceDataCollectionValue $value,
        AssetRepositoryInterface $referenceDataRepository
    ) {
        $attributeCode = 'assets-collection';

        $value->getAttributeCode()->willReturn($attributeCode);
        $value->getData()->willReturn([$attributeCode]);
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('assets');

        $repositoryResolver->resolve('assets')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneByIdentifier($attributeCode)->willReturn($asset);

        $localeRepository->findOneByIdentifier(null)->willReturn(null);
        $asset->getReference(null)->willReturn($reference);
        $reference->getFileInfo()->willReturn($fileInfo);
        $fileNormalizer->normalize($fileInfo)->willReturn(['data file']);

        $this->normalize($value)->shouldReturn(['data file']);
    }

    function it_normalizes_an_asset_from_asset_manager(
        $attributeRepository,
        $repositoryResolver,
        AssetPreviewGenerator $assetPreviewGenerator,
        AssetInterface $asset,
        AttributeInterface $attribute,
        AssetCollectionValue $value,
        AssetRepositoryInterface $referenceDataRepository,
        AssetCode $assetA,
        AssetCode $assetB
    ) {
        $attributeCode = 'assets-manager-collection';

        $assetA->__toString()->willReturn('asset_a');
        $assetB->__toString()->willReturn('asset_b');

        $value->getAttributeCode()->willReturn($attributeCode);
        $value->getData()->willReturn([$assetA, $assetB]);
        $value->getScopeCode()->willReturn('mobile');
        $value->getLocaleCode()->willReturn('en_US');
        $attributeRepository->findOneByIdentifier($attributeCode)->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('packshot');

        $repositoryResolver->resolve('assets')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneByIdentifier($attributeCode)->willReturn($asset);

        $assetPreviewGenerator->getImageUrl(
            'asset_a',
            'packshot',
            'mobile',
            'en_US',
            'thumbnail'
        )->willReturn('/rest/asset_manager/image_preview/AND_OTHER_STUFF');

        $this->normalize($value)->shouldReturn([
            'filePath' => '/rest/asset_manager/image_preview/AND_OTHER_STUFF',
            'originalFilename' => ''
        ]);
    }

    function it_normalizes_an_asset_from_asset_manager_to_null_if_no_data(
        AssetCollectionValue $value
    ) {
        $value->getData()->willReturn([]);

        $this->normalize($value)->shouldReturn(null);
    }

    function it_returns_null_if_there_is_no_data(
        $fileNormalizer,
        $localeRepository,
        $attributeRepository,
        AttributeInterface $attribute,
        Reference $reference,
        FileInfoInterface $fileInfo
    ) {
        $attributeCode = 'assets-collection';

        $value = ReferenceDataCollectionValue::value($attributeCode, []);
        $attributeRepository->findOneByIdentifier('assets-collection')->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('assets');

        $localeRepository->findOneByIdentifier(null)->willReturn(null);
        $reference->getFileInfo()->shouldNotBeCalled();
        $fileNormalizer->normalize($fileInfo)->shouldNotBeCalled();

        $this->normalize($value)->shouldReturn(null);
    }

    function it_returns_null_if_there_is_no_file(
        $fileNormalizer,
        $localeRepository,
        $attributeRepository,
        $repositoryResolver,
        AssetInterface $asset,
        AttributeInterface $attribute,
        Reference $reference,
        FileInfoInterface $fileInfo,
        ReferenceDataCollectionValue $value,
        AssetRepositoryInterface $referenceDataRepository
    ) {
        $attributeCode = 'assets-collection';

        $value->getAttributeCode()->willReturn($attributeCode);
        $value->getData()->willReturn([$attributeCode]);
        $attributeRepository->findOneByIdentifier('assets-collection')->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('assets');

        $repositoryResolver->resolve('assets')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneByIdentifier($attributeCode)->willReturn($asset);

        $localeRepository->findOneByIdentifier(null)->willReturn(null);
        $asset->getReference(null)->willReturn($reference);
        $reference->getFileInfo()->willReturn(null);
        $fileNormalizer->normalize($fileInfo)->shouldNotBeCalled();

        $this->normalize($value)->shouldReturn(null);
    }

    function it_returns_null_if_there_is_no_reference(
        $fileNormalizer,
        $localeRepository,
        $attributeRepository,
        $repositoryResolver,
        AssetInterface $asset,
        AttributeInterface $attribute,
        ReferenceDataCollectionValue $value,
        AssetRepositoryInterface $referenceDataRepository
    ) {
        $attributeCode = 'assets-collection';

        $value->getAttributeCode()->willReturn($attributeCode);
        $value->getData()->willReturn([$attributeCode]);
        $attributeRepository->findOneByIdentifier('assets-collection')->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('assets');

        $repositoryResolver->resolve('assets')->willReturn($referenceDataRepository);
        $referenceDataRepository->findOneByIdentifier($attributeCode)->willReturn($asset);

        $localeRepository->findOneByIdentifier(null)->willReturn(null);
        $asset->getReference(null)->willReturn(null);

        $fileNormalizer->normalize(Argument::any())->shouldNotBeCalled();

        $this->normalize($value)->shouldReturn(null);
    }
}
