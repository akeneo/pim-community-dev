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

namespace Akeneo\EnrichedEntity\Domain\Repository;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

interface AttributeRepositoryInterface
{
    public function create(AbstractAttribute $attribute): void;

    public function update(AbstractAttribute $attribute): void;

    /**
     * @throws AttributeNotFoundException
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute;

    /**
     * @param EnrichedEntityIdentifier $enrichedEntityIdentifier
     *
     * @return AbstractAttribute[]
     */
    public function findByEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): array;
}
