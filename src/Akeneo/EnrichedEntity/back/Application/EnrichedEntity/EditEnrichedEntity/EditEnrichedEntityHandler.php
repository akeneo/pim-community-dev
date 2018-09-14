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

namespace Akeneo\EnrichedEntity\Application\EnrichedEntity\EditEnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditEnrichedEntityHandler
{
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';

    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

    /** @var FileStorerInterface */
    private $storer;

    public function __construct(EnrichedEntityRepositoryInterface $enrichedEntityRepository, FileStorerInterface $storer)
    {
        $this->enrichedEntityRepository = $enrichedEntityRepository;
        $this->storer = $storer;
    }

    public function __invoke(EditEnrichedEntityCommand $editEnrichedEntityCommand): void
    {
        $identifier = EnrichedEntityIdentifier::fromString($editEnrichedEntityCommand->identifier);
        $labelCollection = LabelCollection::fromArray($editEnrichedEntityCommand->labels);

        $enrichedEntity = $this->enrichedEntityRepository->getByIdentifier($identifier);
        $enrichedEntity->updateLabels($labelCollection);

        if (null !== $editEnrichedEntityCommand->image) {
            $existingImage = $enrichedEntity->getImage();
            // If we want to update the image and it's not already in file storage, we store it
            if (
                $existingImage->isEmpty() ||
                $existingImage->getKey() !== $editEnrichedEntityCommand->image['filePath']
            ) {
                $storedFile = $this->storeFile($editEnrichedEntityCommand->image);
                $image = Image::fromFileInfo($storedFile);
                $enrichedEntity->updateImage($image);
            }
        }

        $this->enrichedEntityRepository->update($enrichedEntity);
    }

    private function storeFile(array $image): FileInfoInterface
    {
        $rawFile = new \SplFileInfo($image['filePath']);
        try {
            $file = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
        } catch (FileTransferException | FileRemovalException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        return $file;
    }
}
