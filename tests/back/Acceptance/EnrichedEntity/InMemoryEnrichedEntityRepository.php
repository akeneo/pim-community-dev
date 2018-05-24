<?php
declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryEnrichedEntityRepository implements EnrichedEntityRepository
{
    protected $enrichedEntities = [];

    public function add(EnrichedEntity $enrichedEntity): void
    {
        $this->enrichedEntities[(string) $enrichedEntity->getIdentifier()] = $enrichedEntity;
    }

    public function findOneByIdentifier(EnrichedEntityIdentifier $identifier): ?EnrichedEntity
    {
        return $this->enrichedEntities[(string) $identifier] ?? null;
    }

    public function all(): array
    {
        return array_values($this->enrichedEntities);
    }
}
