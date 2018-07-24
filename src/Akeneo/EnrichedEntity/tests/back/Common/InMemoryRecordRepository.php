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

namespace Akeneo\EnrichedEntity\tests\back\Common;

use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
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
        $key = $this->getKey($record->getIdentifier());
        if (isset($this->records[$key])) {
            throw new \RuntimeException('Record already exists');
        }
        $this->records[$key] = $record;
    }

    public function update(Record $record): void
    {
        $key = $this->getKey($record->getIdentifier());
        if (!isset($this->records[$key])) {
            throw new \RuntimeException('Expected to update one record, but none was saved');
        }
        $this->records[$key] = $record;
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        $key = $this->getKey($identifier);
        if (!isset($this->records[$key])) {
            throw RecordNotFoundException::withIdentifier($identifier);
        }

        return $this->records[$key];
    }

    public function count(): int
    {
        $recordCount = 0;

        foreach ($this->records as $enrichedEntity) {
            $recordCount += count($enrichedEntity);
        }

        return $recordCount;
    }

    private function getKey(RecordIdentifier $recordIdentifier): string
    {
        return sprintf('%s_%s', $recordIdentifier->getEnrichedEntityIdentifier(), $recordIdentifier->getIdentifier());
    }
}
