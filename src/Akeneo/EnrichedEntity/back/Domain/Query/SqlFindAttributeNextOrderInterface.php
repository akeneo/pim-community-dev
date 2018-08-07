<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Query;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

interface SqlFindAttributeNextOrderInterface
{
    public function forEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): int;
}
