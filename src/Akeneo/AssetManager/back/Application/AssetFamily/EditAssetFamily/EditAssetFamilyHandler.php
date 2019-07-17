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
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\File\FileExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAssetFamilyHandler
{
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var FileStorerInterface */
    private $storer;

    /** @var FileExistsInterface */
    private $fileExists;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        FileStorerInterface $storer,
        FileExistsInterface $fileExists
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->storer = $storer;
        $this->fileExists = $fileExists;
    }

    public function __invoke(EditAssetFamilyCommand $editAssetFamilyCommand): void
    {
        $identifier = AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier);
        $labelCollection = LabelCollection::fromArray($editAssetFamilyCommand->labels);
        $ruleTemplateCollection = RuleTemplateCollection::createFromProductLinkRules($editAssetFamilyCommand->ruleTemplates);

        $assetFamily = $this->assetFamilyRepository->getByIdentifier($identifier);
        $assetFamily->updateLabels($labelCollection);
        $assetFamily->updateRuleTemplateCollection($ruleTemplateCollection);

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

        $this->assetFamilyRepository->update($assetFamily);
    }

    private function getStoredImage(array $imageData): Image
    {
        $fileExists = $this->fileExists->exists($imageData['filePath']);

        if (true === $fileExists) {
            $storedFile = (new FileInfo())
                ->setKey($imageData['filePath'])
                ->setOriginalFilename($imageData['originalFilename']);
        } else {
            $rawFile = new \SplFileInfo($imageData['filePath']);
            $storedFile = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
        }

        $storedImage = Image::fromFileInfo($storedFile);

        return $storedImage;
    }
}
