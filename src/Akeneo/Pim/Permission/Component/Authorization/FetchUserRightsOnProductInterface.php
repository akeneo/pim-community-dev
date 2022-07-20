<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Authorization;

use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductUuid;
use Ramsey\Uuid\UuidInterface;

interface FetchUserRightsOnProductInterface
{
    /**
     * @deprecated
     */
    public function fetchByIdentifier(string $productIdentifier, int $userId): UserRightsOnProduct;

    /**
     * @deprecated
     */
    public function fetchByIdentifiers(array $productIdentifiers, int $userId): array;

    public function fetchByUuid(UuidInterface $productUuid, int $userId): UserRightsOnProductUuid;

    /**
     * @param UuidInterface[] $productUuids
     *
     * @return UserRightsOnProductUuid[]
     */
    public function fetchByUuids(array $productUuids, int $userId): array;
}
