<?php

namespace AkeneoEnterprise\Test\Acceptance\Permission\InMemory;

use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class InMemoryProductModelCategoryAccessQuery implements ProductModelCategoryAccessQueryInterface
{
    public function getGrantedProductModelCodes(array $productModelCodes, UserInterface $user): array
    {
        return $productModelCodes;
    }
}
