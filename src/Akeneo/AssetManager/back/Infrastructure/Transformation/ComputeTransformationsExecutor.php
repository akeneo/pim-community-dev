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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\Exception\NonApplicableTransformationException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Webmozart\Assert\Assert;

class ComputeTransformationsExecutor
{
    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var GetTransformations */
    private $getTransformations;

    /** @var GetOutdatedVariationSource */
    private $getOutdatedVariationSource;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FileDownloader */
    private $fileDownloader;

    /** @var FileTransformer */
    private $fileTransformer;

    /** @var FileStorerInterface */
    private $fileStorer;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    public function __construct(
        AssetRepositoryInterface $assetRepository,
        GetTransformations $getTransformations,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        AttributeRepositoryInterface $attributeRepository,
        FileDownloader $fileDownloader,
        FileTransformer $fileTransformer,
        FileStorerInterface $fileStorer,
        EditAssetHandler $editAssetHandler
    ) {
        $this->assetRepository = $assetRepository;
        $this->getTransformations = $getTransformations;
        $this->getOutdatedVariationSource = $getOutdatedVariationSource;
        $this->attributeRepository = $attributeRepository;
        $this->fileDownloader = $fileDownloader;
        $this->fileTransformer = $fileTransformer;
        $this->fileStorer = $fileStorer;
        $this->editAssetHandler = $editAssetHandler;
    }

    /**
     * @param AssetIdentifier[] $assetIdentifiers
     */
    public function execute(array $assetIdentifiers): void
    {
        Assert::allIsInstanceOf($assetIdentifiers, AssetIdentifier::class);
        $transformationsPerAssetIdentifier = $this->getTransformations->fromAssetIdentifiers($assetIdentifiers);

        foreach ($transformationsPerAssetIdentifier as $assetIdentifier => $transformations) {
            $commands = [];
            $asset = $this->assetRepository->getByIdentifier(AssetIdentifier::fromString($assetIdentifier));
            $transformations = $transformationsPerAssetIdentifier[$assetIdentifier] ?? TransformationCollection::noTransformation();

            foreach ($transformations as $transformation) {
                try {
                    $sourceFile = $this->getOutdatedVariationSource->forAssetAndTransformation($asset, $transformation);
                } catch (NonApplicableTransformationException $e) {
                    // TODO: add warning with exception message
                    continue;
                }

                if (null !== $sourceFile) {
                    try {
                        $commands[] = $this->handleTransformation(
                            $sourceFile,
                            $asset->getAssetFamilyIdentifier(),
                            $transformation
                        );
                    } catch (\Exception $e) {
                        // TODO: catch the right execption types
                        // TODO: add warning
                        continue;
                    }
                }
            }
            if (!empty($commands)) {
                ($this->editAssetHandler)(
                    new EditAssetCommand(
                        (string)$asset->getAssetFamilyIdentifier(),
                        (string)$asset->getCode(),
                        $commands
                    )
                );
            }
        }
    }

    private function handleTransformation(
        FileData $sourceFile,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        Transformation $transformation
    ): EditMediaFileValueCommand {
        $file = $this->fileDownloader->get($sourceFile->getKey());
        $transformedFile = $this->fileTransformer->transform($file, $transformation->getOperationCollection());
        $renamedFile = $this->rename($transformedFile, $sourceFile->getOriginalFilename(), $transformation);
        $storedFile = $this->fileStorer->store($renamedFile, Storage::FILE_STORAGE_ALIAS);

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

    private function rename(File $file, string $originalFilename, Transformation $transformation): File
    {
        return $file->move($file->getPath(), $transformation->getTargetFilename($originalFilename));
    }
}
