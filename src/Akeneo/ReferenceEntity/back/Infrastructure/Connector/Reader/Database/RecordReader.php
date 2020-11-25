<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Reader\Database;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordIdentifiersByReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

class RecordReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    private RecordRepositoryInterface $recordRepository;
    private FindRecordIdentifiersByReferenceEntityInterface $findRecordIdentifiers;
    private StepExecution $stepExecution;
    private \Iterator $identifiers;
    private bool $firstRead;

    public function __construct(
        FindRecordIdentifiersByReferenceEntityInterface $findRecordIdentifiers,
        RecordRepositoryInterface $recordRepository
    ) {
        $this->findRecordIdentifiers = $findRecordIdentifiers;
        $this->recordRepository = $recordRepository;
    }

    public function initialize()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString(
            $this->stepExecution->getJobParameters()->get('reference_entity_identifier')
        );

        $this->identifiers = $this->findRecordIdentifiers->find($referenceEntityIdentifier);
        $this->identifiers->rewind();
        $this->firstRead = true;
    }

    public function read()
    {
        $record = null;
        if (!$this->firstRead) {
            $this->identifiers->next();
        }

        if ($this->identifiers->valid()) {
            $record = $this->recordRepository->getByIdentifier($this->identifiers->current());
            if (null !== $record) {
                $this->stepExecution->incrementSummaryInfo('read');
            }
        }

        $this->firstRead = false;

        return $record;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
