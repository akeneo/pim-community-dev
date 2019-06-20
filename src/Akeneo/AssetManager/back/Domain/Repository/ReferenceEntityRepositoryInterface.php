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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface ReferenceEntityRepositoryInterface
{
    public function create(ReferenceEntity $referenceEntity): void;

    public function update(ReferenceEntity $referenceEntity): void;

    /**
     * @throws ReferenceEntityNotFoundException
     */
    public function getByIdentifier(ReferenceEntityIdentifier $identifier): ReferenceEntity;

    public function all(): \Iterator;

    /**
     * @throws ReferenceEntityNotFoundException
     */
    public function deleteByIdentifier(ReferenceEntityIdentifier $identifier): void;

    public function count(): int;
}
