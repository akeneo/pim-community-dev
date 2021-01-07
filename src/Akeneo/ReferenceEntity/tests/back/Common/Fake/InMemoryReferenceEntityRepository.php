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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityCreatedEvent;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryReferenceEntityRepository implements ReferenceEntityRepositoryInterface
{
    /** @var ReferenceEntity[] */
    private $referenceEntities = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(ReferenceEntity $referenceEntity): void
    {
        if (isset($this->referenceEntities[(string) $referenceEntity->getIdentifier()])) {
            throw new \RuntimeException('Reference entity already exists');
        }
        $this->referenceEntities[(string) $referenceEntity->getIdentifier()] = $referenceEntity;

        $this->eventDispatcher->dispatch(
            new ReferenceEntityCreatedEvent($referenceEntity->getIdentifier()),
            ReferenceEntityCreatedEvent::class
        );
    }

    public function update(ReferenceEntity $referenceEntity): void
    {
        if (!isset($this->referenceEntities[(string) $referenceEntity->getIdentifier()])) {
            throw new \RuntimeException('Expected to save one reference entity, but none was saved');
        }
        $this->referenceEntities[(string) $referenceEntity->getIdentifier()] = $referenceEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier(ReferenceEntityIdentifier $identifier): ReferenceEntity
    {
        $referenceEntity = $this->referenceEntities[(string) $identifier] ?? null;
        if (null === $referenceEntity) {
            throw ReferenceEntityNotFoundException::withIdentifier($identifier);
        }

        return $referenceEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdentifier(ReferenceEntityIdentifier $identifier): void
    {
        $referenceEntity = $this->referenceEntities[(string) $identifier] ?? null;
        if (null === $referenceEntity) {
            throw ReferenceEntityNotFoundException::withIdentifier($identifier);
        }

        unset($this->referenceEntities[(string) $identifier]);
    }

    public function count(): int
    {
        return count($this->referenceEntities);
    }

    public function hasReferenceEntity(ReferenceEntityIdentifier $identifier): bool
    {
        return isset($this->referenceEntities[(string) $identifier]);
    }

    public function all(): \Iterator
    {
        return new \ArrayIterator(array_values($this->referenceEntities));
    }
}
