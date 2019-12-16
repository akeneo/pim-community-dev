<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\TransformationExecutor;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\AssetManager\Infrastructure\Transformation\FileTransformer;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class TransformationExecutorSpec extends ObjectBehavior
{
    function let(
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorer $fileStorer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $fileDownloader,
            $fileTransformer,
            $fileStorer,
            $attributeRepository
        );
    }

    function it_is_a_compute_transformation_executor()
    {
        $this->shouldHaveType(TransformationExecutor::class);
    }

    function it_computes_a_transformation(
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorerInterface $fileStorer,
        AttributeRepositoryInterface $attributeRepository,
        Transformation $transformation,
        Operation $operation,
        FileData $sourceFileData,
        FileInfoInterface $storedFile,
        File $transformedFile,
        MediaFileAttribute $thumbnailAttribute
    ) {
        $sourceFileData->getKey()->willReturn('1/2/3/source_file_key');
        $sourceFileData->getOriginalFilename()->willReturn('jambon.png');

        $operations = OperationCollection::create([$operation->getWrappedObject()]);
        $transformation->getOperationCollection()->willReturn($operations);
        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'thumbnail',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            )
        );
        $transformation->getTargetFilename('jambon.png')->willReturn('jambon_thumbnail.png');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('thumbnail'),
            $assetFamilyIdentifier
        )->willReturn($thumbnailAttribute);

        $sourceFile = new File('path/to/jambon.png', false);
        $fileDownloader->get('1/2/3/source_file_key')->willReturn($sourceFile);

        $transformedFile->beConstructedWith(['path/to/jambon_transformed.png', false]);
        $transformedFile->getPath()->willReturn('path/to/');
        $fileTransformer->transform($sourceFile, $operations)->willReturn($transformedFile);
        $transformedFile->move('path/to/', 'jambon_thumbnail.png')->shouldBeCalled()->willReturn($transformedFile);

        $storedFile->getKey()->willReturn('7/5/2/stored_file_key');
        $storedFile->getOriginalFilename()->willReturn('jambon_thumbnail.png');
        $storedFile->getSize()->willReturn(220576);
        $storedFile->getMimeType()->willReturn('image/png');
        $storedFile->getExtension()->willReturn('png');
        $fileStorer->store($transformedFile, Storage::FILE_STORAGE_ALIAS, true)->willReturn($storedFile);

        $command = $this->execute($sourceFileData, $assetFamilyIdentifier, $transformation);

        $command->shouldBeAnInstanceOf(EditMediaFileValueCommand::class);
        $command->updatedAt = null;
        $command->shouldBeLike(
            new EditMediaFileValueCommand(
                $thumbnailAttribute->getWrappedObject(),
                'ecommerce',
                'en_US',
                '7/5/2/stored_file_key',
                'jambon_thumbnail.png',
                220576,
                'image/png',
                'png',
                null
            )
        );
    }
}
