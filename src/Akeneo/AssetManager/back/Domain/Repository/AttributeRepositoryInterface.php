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

namespace Akeneo\ReferenceEntity\Domain\Repository;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface AttributeRepositoryInterface
{
    public function create(AbstractAttribute $attribute): void;

    public function update(AbstractAttribute $attribute): void;

    /**
     * @throws AttributeNotFoundException
     */
    public function deleteByIdentifier(AttributeIdentifier $attributeIdentifier): void;

    /**
     * @throws AttributeNotFoundException
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute;

    /**
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     *
     * @return AbstractAttribute[]
     */
    public function findByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): array;

    /**
     * Count attributes for a given reference entity
     */
    public function countByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int;

    public function nextIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): AttributeIdentifier;
}
