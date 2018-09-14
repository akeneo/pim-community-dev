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

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater\ValueUpdaterRegistryInterface;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
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

    /** @var ValueUpdaterRegistryInterface  */
    private $valueUpdaterRegistry;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var FileStorerInterface */
    private $storer;

    public function __construct(
        ValueUpdaterRegistryInterface $valueUpdaterRegistry,
        RecordRepositoryInterface $recordRepository,
        FileStorerInterface $storer
    ) {
        $this->valueUpdaterRegistry = $valueUpdaterRegistry;
        $this->recordRepository = $recordRepository;
        $this->storer = $storer;
    }

    public function __invoke(EditRecordCommand $editRecordCommand): void
    {
        $record = $this->getRecord($editRecordCommand);
        $this->editLabels($record, $editRecordCommand);
        $this->editValues($record, $editRecordCommand);

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

    private function getRecord(EditRecordCommand $editRecordCommand): Record
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($editRecordCommand->enrichedEntityIdentifier);
        $code = RecordCode::fromString($editRecordCommand->code);
        $record = $this->recordRepository->getByEnrichedEntityAndCode($enrichedEntityIdentifier, $code);

        return $record;
    }

    private function editLabels(Record $record, EditRecordCommand $editRecordCommand): void
    {
        $labelCollection = LabelCollection::fromArray($editRecordCommand->labels);
        $record->setLabels($labelCollection);
    }

    private function editValues(Record $record, EditRecordCommand $editRecordCommand): void
    {
        foreach ($editRecordCommand->editRecordValueCommands as $editRecordValueCommand) {
            $editValueUpdater = $this->valueUpdaterRegistry->getUpdater($editRecordValueCommand);
            ($editValueUpdater)($record, $editRecordValueCommand);
        }
    }
}
