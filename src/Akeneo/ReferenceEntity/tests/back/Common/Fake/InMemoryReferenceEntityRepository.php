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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryReferenceEntityRepository implements ReferenceEntityRepositoryInterface
{
    /** @var ReferenceEntity[] */
    private $referenceEntities = [];

    public function create(ReferenceEntity $referenceEntity): void
    {
        if (isset($this->referenceEntities[(string) $referenceEntity->getIdentifier()])) {
            throw new \RuntimeException('Reference entity already exists');
        }
        $this->referenceEntities[(string) $referenceEntity->getIdentifier()] = $referenceEntity;
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

    public function hasRecord(ReferenceEntityIdentifier $identifier): bool
    {
        return isset($this->referenceEntities[(string) $identifier]);
    }
}
