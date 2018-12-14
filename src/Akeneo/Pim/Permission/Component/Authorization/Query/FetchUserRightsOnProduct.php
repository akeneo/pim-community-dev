<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Authorization\Query;

use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;

interface FetchUserRightsOnProduct
{
    public function fetch(string $userIdentifier, int $userId): UserRightsOnProduct;
}
