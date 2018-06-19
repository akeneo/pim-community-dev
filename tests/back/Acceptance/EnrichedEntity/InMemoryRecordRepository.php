<?php
declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\RecordRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryRecordRepository implements RecordRepository
{
    /** @var Record[] */
    protected $records = [];

    public function save(Record $record): void
    {
        $this->records[(string) $record->getIdentifier()] = $record;
    }

    public function getByIdentifier(RecordIdentifier $identifier): ?Record
    {
        return $this->records[(string) $identifier] ?? null;
    }
}
