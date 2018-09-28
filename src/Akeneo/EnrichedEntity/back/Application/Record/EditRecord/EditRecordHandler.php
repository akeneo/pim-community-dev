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

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord;

use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditRecordHandler
{
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var FileStorerInterface */
    private $storer;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        FileStorerInterface $storer
    ) {
        $this->recordRepository = $recordRepository;
        $this->storer = $storer;
    }

    public function __invoke(EditRecordCommand $editRecordCommand): void
    {
        $identifier = RecordIdentifier::fromString($editRecordCommand->identifier);
        $labelCollection = LabelCollection::fromArray($editRecordCommand->labels);

        $record = $this->recordRepository->getByIdentifier($identifier);
        $record->setLabels($labelCollection);

        if (null !== $editRecordCommand->image) {
            $existingImage = $record->getImage();
            if (
                $existingImage->isEmpty() ||
                $existingImage->getKey() !== $editRecordCommand->image['filePath']
            ) {
                $image = $editRecordCommand->image;
                $rawFile = new \SplFileInfo($image['filePath']);
                $file = $this->storer->store($rawFile, self::CATALOG_STORAGE_ALIAS);
                $image = Image::fromFileInfo($file);
                $record->updateImage($image);
            }
        } else {
            $record->updateImage(Image::createEmpty());
        }

        $this->recordRepository->update($record);
    }
}
