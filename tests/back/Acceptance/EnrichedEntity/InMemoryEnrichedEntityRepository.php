<?php
declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Akeneo\EnrichedEntity\back\Domain\Repository\EntityNotFoundException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryEnrichedEntityRepository implements EnrichedEntityRepository
{
    private $enrichedEntities = [];

    public function save(EnrichedEntity $enrichedEntity): void
    {
        $this->enrichedEntities[(string) $enrichedEntity->getIdentifier()] = $enrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity
    {
        $enrichedEntity = $this->enrichedEntities[(string) $identifier] ?? null;
        if (null === $enrichedEntity) {
            throw EntityNotFoundException::withIdentifier(EnrichedEntity::class, (string) $identifier);
        }

        return $enrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return array_values($this->enrichedEntities);
    }
}
