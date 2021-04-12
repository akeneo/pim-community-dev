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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryRecordRepository implements RecordRepositoryInterface
{
    /** @var Record[] */
    protected $records = [];

    public function create(Record $record): void
    {
        if (isset($this->records[$record->getIdentifier()->__toString()])) {
            throw new \RuntimeException('Record already exists');
        }

        try {
            $this->getByReferenceEntityAndCode($record->getReferenceEntityIdentifier(), $record->getCode());
        } catch (RecordNotFoundException $exception) {
            $this->records[$record->getIdentifier()->__toString()] = $record;

            return;
        }

        throw new \RuntimeException('Record already exists');
    }

    public function update(Record $record): void
    {
        if (!isset($this->records[$record->getIdentifier()->__toString()])) {
            throw new \RuntimeException('Expected to update one record, but none was saved');
        }

        $this->records[$record->getIdentifier()->__toString()] = $record;
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        if (!isset($this->records[$identifier->__toString()])) {
            throw RecordNotFoundException::withIdentifier($identifier);
        }

        return $this->records[$identifier->__toString()];
    }

    public function getByReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): Record {
        foreach ($this->records as $record) {
            if ($record->getCode()->equals($code) && $record->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)) {
                return $record;
            }
        }

        throw RecordNotFoundException::withReferenceEntityAndCode($referenceEntityIdentifier, $code);
    }

    public function getByReferenceEntityAndCodes(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        array $recordCodes
    ): array {
        $recordsFound = [];

        foreach ($this->records as $record) {
            foreach ($recordCodes as $recordCode) {
                if ($record->getCode()->equals(RecordCode::fromString($recordCode)) && $record->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)) {
                    $recordsFound[] = $record;
                }
            }
        }

        return $recordsFound;
    }

    public function deleteByReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): void {
        foreach ($this->records as $index => $record) {
            if ($record->getCode()->equals($code) && $record->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)) {
                unset($this->records[$index]);

                return;
            }
        }

        throw RecordNotFoundException::withReferenceEntityAndCode($referenceEntityIdentifier, $code);
    }

    public function deleteByReferenceEntityAndCodes(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        array $recordCodes
    ): void {
        foreach ($recordCodes as $recordCode) {
            $this->deleteByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        }
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function nextIdentifier(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): RecordIdentifier {
        return RecordIdentifier::create(
            $referenceEntityIdentifier->__toString(),
            $code->__toString(),
            Uuid::uuid4()->toString()
        );
    }

    public function hasRecord(RecordIdentifier $identifier)
    {
        return isset($this->records[$identifier->__toString()]);
    }

    public function referenceEntityHasRecords(ReferenceEntityIdentifier $referenceEntityIdentifier)
    {
        foreach ($this->records as $record) {
            if ($record->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Record[]
     */
    public function all(): array
    {
        return $this->records;
    }

    public function countByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int
    {
        $count = 0;
        foreach ($this->records as $record) {
            if ($record->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)) {
                $count++;
            }
        }

        return $count;
    }
}
