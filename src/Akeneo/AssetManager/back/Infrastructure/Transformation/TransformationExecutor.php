<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileTargetValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationException;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\TransformationFailedException;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class TransformationExecutor
{
    /** @var Filesystem */
    private $filesystem;

    /** @var FileDownloader */
    private $fileDownloader;

    /** @var FileTransformer */
    private $fileTransformer;

    /** @var FileStorerInterface */
    private $fileStorer;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var array */
    private $currentSourceFile = [
        'key' => null,
        'path' => null,
    ];

    public function __construct(
        Filesystem $filesystem,
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorerInterface $fileStorer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->filesystem = $filesystem;
        $this->fileDownloader = $fileDownloader;
        $this->fileTransformer = $fileTransformer;
        $this->fileStorer = $fileStorer;
        $this->attributeRepository = $attributeRepository;
    }

    public function execute(
        FileData $sourceFileData,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        Transformation $transformation,
        string $workingDirectory
    ): EditMediaFileTargetValueCommand {
        try {
            $sourceFile = $this->getSourceFile(
                $sourceFileData->getKey(),
                $workingDirectory,
                $sourceFileData->getOriginalFilename()
            );
            $transformedFile = $this->fileTransformer->transform($sourceFile, $transformation);
            $storedFile = $this->fileStorer->store($transformedFile, Storage::FILE_STORAGE_ALIAS, true);

            $target = $transformation->getTarget();
            $targetAttribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                $target->getAttributeCode(),
                $assetFamilyIdentifier
            );
        } catch (FileTransferException | TransformationException | FileRemovalException | IOExceptionInterface $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode());
        }

        return new EditMediaFileTargetValueCommand(
            $targetAttribute,
            $target->getChannelReference()->normalize(),
            $target->getLocaleReference()->normalize(),
            $storedFile->getKey(),
            $storedFile->getOriginalFilename(),
            $storedFile->getSize(),
            $storedFile->getMimeType(),
            $storedFile->getExtension(),
            (new \DateTimeImmutable())->format(\DateTimeInterface::ISO8601)
        );
    }

    private function getSourceFile(string $fileKey, string $workingDirectory, string $originalFilename): File
    {
        if ($fileKey !== $this->currentSourceFile['key']) {
            if (null !== $this->currentSourceFile['path'] && $this->filesystem->exists($this->currentSourceFile['path'])) {
                $this->filesystem->remove($this->currentSourceFile['path']);
            }
            $this->currentSourceFile = [
                'key' => $fileKey,
                'path' => $this->fileDownloader->get($fileKey, $workingDirectory, $originalFilename),
            ];
        }

        return new File($this->currentSourceFile['path'], false);
    }
}
