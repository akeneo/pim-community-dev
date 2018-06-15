<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Repository;

use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\back\Domain\Model\Record\RecordIdentifier;

interface RecordRepository
{
    public function save(Record $record): void;

    public function getByIdentifier(RecordIdentifier $identifier): ?Record;

    public function all(): array;
}
