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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Symfony\Component\HttpFoundation\File\File;

class TransformationExecutor
{
    /** @var FileDownloader */
    private $fileDownloader;

    /** @var FileTransformer */
    private $fileTransformer;

    /** @var FileStorerInterface */
    private $fileStorer;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorerInterface $fileStorer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->fileDownloader = $fileDownloader;
        $this->fileTransformer = $fileTransformer;
        $this->fileStorer = $fileStorer;
        $this->attributeRepository = $attributeRepository;
    }

    public function execute(
        FileData $sourceFileData,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        Transformation $transformation
    ): EditMediaFileValueCommand {
        $sourceFile = $this->getSourceFile($sourceFileData->getKey());
        $transformedFile = $this->fileTransformer->transform(
            $sourceFile,
            $transformation->getOperationCollection()
        );
        $renamedFile = $this->rename($transformedFile, $sourceFileData->getOriginalFilename(), $transformation);
        $storedFile = $this->fileStorer->store($renamedFile, Storage::FILE_STORAGE_ALIAS, true);

        $target = $transformation->getTarget();
        $targetAttribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            $target->getAttributeCode(),
            $assetFamilyIdentifier
        );

        return new EditMediaFileValueCommand(
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

    private function getSourceFile($fileKey): File
    {
        return $this->fileDownloader->get($fileKey);
    }

    private function rename(File $file, string $originalFilename, Transformation $transformation): File
    {
        return $file->move($file->getPath(), $transformation->getTargetFilename($originalFilename));
    }
}
