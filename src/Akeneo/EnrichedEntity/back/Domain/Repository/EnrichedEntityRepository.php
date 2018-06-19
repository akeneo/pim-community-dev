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
