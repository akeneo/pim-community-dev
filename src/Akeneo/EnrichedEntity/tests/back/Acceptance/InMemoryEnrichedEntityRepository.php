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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepository;
use Akeneo\EnrichedEntity\Domain\Repository\EntityNotFoundException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryEnrichedEntityRepository implements EnrichedEntityRepository
{
    /** @var EnrichedEntity[] */
    private $enrichedEntities = [];

    public function save(EnrichedEntity $enrichedEntity): void
    {
        $this->enrichedEntities[(string) $enrichedEntity->getIdentifier()] = $enrichedEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity
    {
        $enrichedEntity = $this->enrichedEntities[(string) $identifier] ?? null;
        if (null === $enrichedEntity) {
            throw EntityNotFoundException::withIdentifier(EnrichedEntity::class, (string) $identifier);
        }

        return $enrichedEntity;
    }
}
