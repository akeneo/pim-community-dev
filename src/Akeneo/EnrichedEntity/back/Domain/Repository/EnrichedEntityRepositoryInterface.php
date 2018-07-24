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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;

interface EnrichedEntityRepositoryInterface
{
    public function create(EnrichedEntity $enrichedEntity): void;

    public function update(EnrichedEntity $enrichedEntity): void;

    /**
     * @throws EnrichedEntityNotFoundException
     */
    public function getByIdentifier(EnrichedEntityIdentifier $identifier): EnrichedEntity;
}
