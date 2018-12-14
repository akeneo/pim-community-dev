<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Authorization\Query;

use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;

interface FetchUserRightsOnProductModel
{
    public function fetch(string $productModelCode, int $userId): UserRightsOnProductModel;
}
