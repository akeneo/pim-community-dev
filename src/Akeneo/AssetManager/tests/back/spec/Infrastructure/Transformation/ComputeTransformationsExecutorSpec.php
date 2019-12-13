<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\AssetManager\Infrastructure\Transformation\FileTransformer;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;

class ComputeTransformationsExecutorSpec extends ObjectBehavior
{
    function let(
        AssetRepositoryInterface $assetRepository,
        GetTransformations $getTransformations,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        AttributeRepositoryInterface $attributeRepository,
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorerInterface $fileStorer,
        EditAssetHandler $editAssetHandler
    ) {
        $this->beConstructedWith(
            $assetRepository,
            $getTransformations,
            $getOutdatedVariationSource,
            $attributeRepository,
            $fileDownloader,
            $fileTransformer,
            $fileStorer,
            $editAssetHandler
        );
    }

    function it_is_a_compute_transformation_executor()
    {
        $this->shouldHaveType(ComputeTransformationsExecutor::class);
    }

    function it_only_accepts_asset_identifiers()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('execute', [[new \stdClass()]]);
    }

    function it_computes_transformations(
        AssetRepositoryInterface $assetRepository,
        GetTransformations $getTransformations,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        AttributeRepositoryInterface $attributeRepository,
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorerInterface $fileStorer,
        EditAssetHandler $editAssetHandler,
        Asset $asset,
        FileData $sourceFileData,
        File $sourceFile,
        FileInfoInterface $storedFileInfo,
        MediaFileAttribute $targetAttribute
    ) {
        $assetIdentifier = AssetIdentifier::fromString('packshot_assetcode_123456');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $asset->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $asset->getCode()->willReturn(AssetCode::fromString('assetcode'));
        $assetRepository->getByIdentifier($assetIdentifier)->willReturn($asset);

        $transformation = Transformation::create(
            TransformationLabel::fromString('label'),
            Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
            Target::createFromNormalized(['attribute' => 'thumbnail', 'channel' => null, 'locale' => null]),
            OperationCollection::create([ThumbnailOperation::create(['width' => 100, 'height' => 80])]),
            '',
            '_thumbnail',
            new \DateTime()
        );
        $getTransformations->fromAssetIdentifiers([$assetIdentifier])->willReturn(
            [
                'packshot_assetcode_123456' => TransformationCollection::create([$transformation]),
            ]
        );

        $getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation)->willReturn($sourceFileData);

        $sourceFileData->getKey()->willReturn('stored_file_key');
        $sourceFileData->getOriginalFilename()->willreturn('jambon.png');

        $sourceFile->beConstructedWith(['/my/local/path/to/jambon.png', false]);
        $sourceFile->getPath()->willReturn('/my/local/path/to');
        $fileDownloader->get('stored_file_key')->willReturn($sourceFile);
        $fileTransformer->transform($sourceFile, Argument::type(OperationCollection::class))->willReturn($sourceFile);

        $sourceFile->move('/my/local/path/to', 'jambon_thumbnail.png')->shouldBeCalled()->willReturn($sourceFile);

        $storedFileInfo->getKey()->willReturn('transformed_file_key');
        $storedFileInfo->getOriginalFilename()->willReturn('jambon_thumbnail.png');
        $storedFileInfo->getSize()->willReturn(123456);
        $storedFileInfo->getMimeType()->willReturn('image/png');
        $storedFileInfo->getExtension()->willReturn('png');

        $fileStorer->store($sourceFile, Storage::FILE_STORAGE_ALIAS, true)
                   ->shouldBeCalled()->willReturn($storedFileInfo);
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('thumbnail'),
            $assetFamilyIdentifier
        )->willReturn($targetAttribute);

        $editAssetHandler->__invoke(Argument::type(EditAssetCommand::class))->shouldBeCalled();

        $this->execute([$assetIdentifier]);
    }
}
