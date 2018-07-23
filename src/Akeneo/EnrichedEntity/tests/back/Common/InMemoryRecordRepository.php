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

    public function save(Record $record): void
    {
        $identifier = $record->getIdentifier();
        $this->records[$identifier->getEnrichedEntityIdentifier()][$identifier->getIdentifier()] = $record;
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record {
        $recordIdentifier = $identifier->getIdentifier();
        $enrichedEntityIdentifier = $identifier->getEnrichedEntityIdentifier();
        if (!isset($this->records[$enrichedEntityIdentifier][$recordIdentifier])) {
            throw RecordNotFoundException::withIdentifier($enrichedEntityIdentifier, $recordIdentifier);
        }

        return $this->records[$enrichedEntityIdentifier][$recordIdentifier];
    }

    public function count(): int
    {
        $recordCount = 0;

        foreach ($this->records as $enrichedEntity) {
            $recordCount += count($enrichedEntity);
        }

        return $recordCount;
    }
}
