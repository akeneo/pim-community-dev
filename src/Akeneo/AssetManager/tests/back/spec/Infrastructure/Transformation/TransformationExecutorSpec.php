<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileTargetValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationFailedException;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\AssetManager\Infrastructure\Transformation\FileTransformer;
use Akeneo\AssetManager\Infrastructure\Transformation\TransformationExecutor;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class TransformationExecutorSpec extends ObjectBehavior
{
    function let(
        Filesystem $filesystem,
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorer $fileStorer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $filesystem,
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
        FileData $sourceFileData,
        FileInfoInterface $storedFile,
        MediaFileAttribute $thumbnailAttribute
    ) {
        $sourceFileData->getKey()->willReturn('1/2/3/source_file_key');
        $sourceFileData->getOriginalFilename()->willReturn('jambon.png');

        $transformation->getTarget()->willReturn(
            Target::createFromNormalized(
                [
                    'attribute' => 'thumbnail',
                    'channel' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            )
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('thumbnail'),
            $assetFamilyIdentifier
        )->willReturn($thumbnailAttribute);

        $sourceFile = new File('/job/source/dir/jambon.png', false);
        $fileDownloader->get('1/2/3/source_file_key', '/job/source/dir', 'jambon.png')->willReturn($sourceFile);

        $transformedFile = new File('/job/source/dir/jambon_transformed.png', false);
        $fileTransformer->transform($sourceFile, $transformation)->willReturn($transformedFile);

        $storedFile->getKey()->willReturn('7/5/2/stored_file_key');
        $storedFile->getOriginalFilename()->willReturn('jambon_thumbnail.png');
        $storedFile->getSize()->willReturn(220576);
        $storedFile->getMimeType()->willReturn('image/png');
        $storedFile->getExtension()->willReturn('png');
        $fileStorer->store($transformedFile, Storage::FILE_STORAGE_ALIAS, true)->willReturn($storedFile);

        $command = $this->execute($sourceFileData, $assetFamilyIdentifier, $transformation, '/job/source/dir');

        $command->shouldBeAnInstanceOf(EditMediaFileTargetValueCommand::class);
        $command->updatedAt = null;
        $command->shouldBeLike(
            new EditMediaFileTargetValueCommand(
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

    function it_does_not_download_the_same_source_file_twice(
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileData $sourceFileData,
        Transformation $transformation1,
        Transformation $transformation2
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('test');
        $sourceFileData->getOriginalFilename()->willReturn('jambon.jpg');
        $sourceFileData->getKey()->willReturn('stored_file_key');
        $fileTransformer->transform(Argument::cetera())->shouldBeCalledTimes(2)->willThrow(
            new TransformationException()
        );

        $fileDownloader->get('stored_file_key', '/temporary/dir', 'jambon.jpg')->shouldBeCalledOnce();

        $this->shouldThrow(TransformationFailedException::class)->during(
            'execute',
            [
                $sourceFileData,
                $assetFamilyIdentifier,
                $transformation1,
                '/temporary/dir',
            ]
        );
        $this->shouldThrow(TransformationFailedException::class)->during(
            'execute',
            [
                $sourceFileData,
                $assetFamilyIdentifier,
                $transformation2,
                '/temporary/dir'
            ]
        );
    }

    function it_removes_the_previous_source_file(
        Filesystem $filesystem,
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileData $sourceFileData1,
        FileData $sourceFileData2,
        Transformation $transformation1,
        Transformation $transformation2
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('test');

        $sourceFileData1->getOriginalFilename()->willReturn('jambon.jpg');
        $sourceFileData1->getKey()->willReturn('stored_file_key');
        $sourceFile1 = new File('/temporary/dir/jambon.jpg', false);
        $fileDownloader->get('stored_file_key', '/temporary/dir', 'jambon.jpg')->willReturn($sourceFile1);

        $sourceFileData2->getOriginalFilename()->willReturn('lardons.jpg');
        $sourceFileData2->getKey()->willReturn('other_stored_file_key');
        $sourceFile2 = new File('/temporary/dir/lardons.jpg', false);
        $fileDownloader->get('other_stored_file_key', '/temporary/dir', 'lardons.jpg')->willReturn($sourceFile2);

        $fileTransformer->transform(Argument::cetera())->shouldBeCalledTimes(2)->willThrow(
            new TransformationException()
        );

        $filesystem->exists('/temporary/dir/jambon.jpg')->willReturn(true);
        $filesystem->remove('/temporary/dir/jambon.jpg')->shouldBeCalled();

        $this->shouldThrow(TransformationFailedException::class)->during(
            'execute',
            [
                $sourceFileData1,
                $assetFamilyIdentifier,
                $transformation1,
                '/temporary/dir',
            ]
        );
        $this->shouldThrow(TransformationFailedException::class)->during(
            'execute',
            [
                $sourceFileData2,
                $assetFamilyIdentifier,
                $transformation2,
                '/temporary/dir'
            ]
        );
    }
}
