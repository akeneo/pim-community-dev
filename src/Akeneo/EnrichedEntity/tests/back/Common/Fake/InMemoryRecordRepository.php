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

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryRecordRepository implements RecordRepositoryInterface
{
    /** @var array */
    protected $records = [];

    public function create(Record $record): void
    {
        if (isset($this->records[$record->getIdentifier()->__toString()])) {
            throw new \RuntimeException('Record already exists');
        }
        $this->records[$record->getIdentifier()->__toString()] = $record;
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

    public function getByEnrichedEntityAndCode(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): Record
    {
        foreach ($this->records as $record) {
            if ($record->getCode()->equals($code) && $record->getEnrichedEntityIdentifier()->equals($enrichedEntityIdentifier)) {
                return $record;
            }
        }

        throw RecordNotFoundException::withEnrichedEntityAndCode($enrichedEntityIdentifier, $code);
    }

    public function count(): int
    {
        $recordCount = 0;

        foreach ($this->records as $enrichedEntity) {
            $recordCount += count($enrichedEntity);
        }

        return $recordCount;
    }

    public function hasRecord(RecordIdentifier $identifier)
    {
        return isset($this->records[$identifier->__toString()]);
    }

    public function enrichedEntityHasRecords(EnrichedEntityIdentifier $enrichedEntityIdentifier)
    {
        foreach ($this->records as $record) {
            if ($record->getEnrichedEntityIdentifier()->equals($enrichedEntityIdentifier)) {
                return true;
            }
        }

        return false;
    }

    public function nextIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): RecordIdentifier
    {
        return RecordIdentifier::create(
            $enrichedEntityIdentifier->__toString(),
            $code->__toString(),
            md5('fingerprint')
        );
    }
}
