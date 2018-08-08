<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Query;

use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;

interface ExistsRecordInterface
{
    public function withIdentifier(RecordIdentifier $recordIdentifier): bool;
}
