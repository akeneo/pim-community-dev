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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryRecordRepository implements RecordRepositoryInterface
{
    /** @var Record[] */
    protected $records = [];

    public function save(Record $record): void
    {
        $recordIdentifier = (string) $record->getIdentifier();
        $enrichedEntityIdentifier = (string) $record->getEnrichedEntityIdentifier();

        $this->records[$enrichedEntityIdentifier][$recordIdentifier] = $record;
    }

    public function getByIdentifier(
    ): ?Record {
        RecordIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier
        if (!isset($this->records[(string) $enrichedEntityIdentifier])) {
            return null;
        }

        $records = $this->records[(string) $enrichedEntityIdentifier];

        return $records[(string) $identifier] ?? null;
    }
}
