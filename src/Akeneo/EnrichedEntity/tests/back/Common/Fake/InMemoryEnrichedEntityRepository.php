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

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryEnrichedEntityRepository implements EnrichedEntityRepositoryInterface
{
    /** @var EnrichedEntity[] */
    private $enrichedEntities = [];

    public function create(EnrichedEntity $enrichedEntity): void
    {
        if (isset($this->enrichedEntities[(string) $enrichedEntity->getIdentifier()])) {
            throw new \RuntimeException('Enriched entity already exists');
        }
        $this->enrichedEntities[(string) $enrichedEntity->getIdentifier()] = $enrichedEntity;
    }

    public function update(EnrichedEntity $enrichedEntity): void
    {
        if (!isset($this->enrichedEntities[(string) $enrichedEntity->getIdentifier()])) {
            throw new \RuntimeException('Expected to save one enriched entity, but none was saved');
        }
        $this->enrichedEntities[(string) $enrichedEntity->getIdentifier()] = $enrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity
    {
        $enrichedEntity = $this->enrichedEntities[(string) $identifier] ?? null;
        if (null === $enrichedEntity) {
            throw EnrichedEntityNotFoundException::withIdentifier($identifier);
        }

        return $enrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdentifier(EnrichedEntityIdentifier $identifier): void
    {
        $enrichedEntity = $this->enrichedEntities[(string) $identifier] ?? null;
        if (null === $enrichedEntity) {
            throw EnrichedEntityNotFoundException::withIdentifier($identifier);
        }

        unset($this->enrichedEntities[(string) $identifier]);
    }

    public function count(): int
    {
        return count($this->enrichedEntities);
    }

    public function hasRecord(EnrichedEntityIdentifier $identifier): bool
    {
        return isset($this->enrichedEntities[(string) $identifier]);
    }
}
