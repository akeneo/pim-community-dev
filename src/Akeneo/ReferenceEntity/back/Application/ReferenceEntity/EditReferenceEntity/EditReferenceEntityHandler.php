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

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditReferenceEntityHandler
{
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var FileStorerInterface */
    private $storer;

    public function __construct(ReferenceEntityRepositoryInterface $referenceEntityRepository, FileStorerInterface $storer)
    {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->storer = $storer;
    }

    public function __invoke(EditReferenceEntityCommand $editReferenceEntityCommand): void
    {
        $identifier = ReferenceEntityIdentifier::fromString($editReferenceEntityCommand->identifier);
        $labelCollection = LabelCollection::fromArray($editReferenceEntityCommand->labels);

        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($identifier);
        $referenceEntity->updateLabels($labelCollection);

        if (null !== $editReferenceEntityCommand->image) {
            $existingImage = $referenceEntity->getImage();
            // If we want to update the image and it's not already in file storage, we store it
            if (
                $existingImage->isEmpty() ||
                $existingImage->getKey() !== $editReferenceEntityCommand->image['filePath']
            ) {

                $image = $editReferenceEntityCommand->image;
                $rawFile = new \SplFileInfo($image['filePath']);
                $storedFile = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
                $image = Image::fromFileInfo($storedFile);
                $referenceEntity->updateImage($image);
            }
        } else {
            $referenceEntity->updateImage(Image::createEmpty());
        }

        $this->referenceEntityRepository->update($referenceEntity);
    }
}
