<?php
declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\RecordRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryRecordRepository implements RecordRepository
{
    protected $records = [];

    public function add(Record $record): void
    {
        $this->records[(string) $record->getIdentifier()] = $record;
    }

    public function findOneByIdentifier(RecordIdentifier $identifier): ?Record
    {
        return $this->records[(string) $identifier] ?? null;
    }

    public function all(): array
    {
        return array_values($this->records);
    }
}
