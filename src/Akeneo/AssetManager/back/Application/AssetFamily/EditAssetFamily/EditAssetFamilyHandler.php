<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeIdentifierInterface;
use Akeneo\AssetManager\Domain\Query\ClockInterface;
use Akeneo\AssetManager\Domain\Query\File\FileExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAssetFamilyHandler
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var FileStorerInterface */
    private $storer;

    /** @var FileExistsInterface */
    private $fileExists;

    /** @var GetAttributeIdentifierInterface */
    private $getAttributeIdentifier;

    /** @var TransformationCollectionFactory */
    private $transformationCollectionFactory;

    /** @var ClockInterface */
    private $clock;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        GetAttributeIdentifierInterface $getAttributeIdentifier,
        FileStorerInterface $storer,
        FileExistsInterface $fileExists,
        TransformationCollectionFactory $transformationCollectionFactory,
        ClockInterface $clock
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->storer = $storer;
        $this->fileExists = $fileExists;
        $this->transformationCollectionFactory = $transformationCollectionFactory;
        $this->clock = $clock;
    }

    public function __invoke(EditAssetFamilyCommand $editAssetFamilyCommand): void
    {
        $identifier = AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier);
        $labelCollection = LabelCollection::fromArray($editAssetFamilyCommand->labels);

        $assetFamily = $this->assetFamilyRepository->getByIdentifier($identifier);
        $assetFamily->updateLabels($labelCollection);

        if (null !== $editAssetFamilyCommand->image) {
            $existingImage = $assetFamily->getImage();
            // If we want to update the image and it's not already in file storage, we store it if needed
            if (
                $existingImage->isEmpty() ||
                $existingImage->getKey() !== $editAssetFamilyCommand->image['filePath']
            ) {
                $image = $this->getStoredImage($editAssetFamilyCommand->image);
                $assetFamily->updateImage($image);
            }
        } else {
            $assetFamily->updateImage(Image::createEmpty());
        }

        $attributeAsMainMedia = $editAssetFamilyCommand->attributeAsMainMedia;
        if (null !== $attributeAsMainMedia) {
            $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
                AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier),
                AttributeCode::fromString($attributeAsMainMedia)
            );
            $assetFamily->updateAttributeAsMainMediaReference(
                AttributeAsMainMediaReference::fromAttributeIdentifier($attributeAsMainMediaIdentifier)
            );
        }

        if (null !== $editAssetFamilyCommand->productLinkRules) {
            $ruleTemplateCollection = RuleTemplateCollection::createFromProductLinkRules($editAssetFamilyCommand->productLinkRules);
            $assetFamily->updateRuleTemplateCollection($ruleTemplateCollection);
        }

        if (null !== $editAssetFamilyCommand->transformations) {
            $assetFamily->getTransformationCollection()->update(
                $this->computeUpdatedTransformationCollection($editAssetFamilyCommand->transformations)
            );
        }

        if (null !== $editAssetFamilyCommand->namingConvention) {
            $assetFamily->updateNamingConvention(
                NamingConvention::createFromNormalized($editAssetFamilyCommand->namingConvention)
            );
        }

        $this->assetFamilyRepository->update($assetFamily);
    }

    private function getStoredImage(array $imageData): Image
    {
        $fileExists = $this->fileExists->exists($imageData['filePath']);

        if (true === $fileExists) {
            $mediaFile = (new FileInfo())
                ->setKey($imageData['filePath'])
                ->setOriginalFilename($imageData['originalFilename']);
        } else {
            $rawFile = new \SplFileInfo($imageData['filePath']);
            $mediaFile = $this->storer->store($rawFile, Storage::FILE_STORAGE_ALIAS);
        }

        $storedImage = Image::fromFileInfo($mediaFile);

        return $storedImage;
    }

    private function computeUpdatedTransformationCollection(array $normalizedTransformations): TransformationCollection
    {
        $formattedDate = $this->clock->now()->format(\DateTimeInterface::ISO8601);
        foreach ($normalizedTransformations as &$normalizedTransformation) {
            $normalizedTransformation['updated_at'] = $formattedDate;
        }

        return $this->transformationCollectionFactory->fromNormalized($normalizedTransformations);
    }
}
