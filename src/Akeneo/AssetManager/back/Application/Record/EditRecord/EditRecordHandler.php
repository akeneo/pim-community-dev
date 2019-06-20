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

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\ValueUpdater\ValueUpdaterRegistryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;

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

    /**
     * @param EditRecordCommand $editRecordCommand
     *
     * @throws FileRemovalException
     * @throws FileTransferException
     */
    public function __invoke(EditRecordCommand $editRecordCommand): void
    {
        $record = $this->getRecord($editRecordCommand);
        $this->editValues($record, $editRecordCommand);

        $this->recordRepository->update($record);
    }

    private function getRecord(EditRecordCommand $editRecordCommand): Record
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($editRecordCommand->referenceEntityIdentifier);
        $code = RecordCode::fromString($editRecordCommand->code);
        $record = $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $code);

        return $record;
    }

    private function editValues(Record $record, EditRecordCommand $editRecordCommand): void
    {
        foreach ($editRecordCommand->editRecordValueCommands as $editRecordValueCommand) {
            $editValueUpdater = $this->valueUpdaterRegistry->getUpdater($editRecordValueCommand);
            ($editValueUpdater)($record, $editRecordValueCommand);
        }
    }
}
