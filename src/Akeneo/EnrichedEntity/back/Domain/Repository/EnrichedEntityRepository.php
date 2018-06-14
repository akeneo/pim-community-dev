<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Repository;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

interface EnrichedEntityRepository
{
    public function save(EnrichedEntity $enrichedEntity): void;

    /**
     * @throws EntityNotFoundException
     */
    public function getByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity;

    /**
     * @return EnrichedEntity[]
     */
    public function all(): array;
}
