<?php

namespace AkeneoEnterprise\Test\Acceptance\Permission\InMemory;

use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class InMemoryProductCategoryAccessQuery implements ProductCategoryAccessQueryInterface
{
    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array
    {
        return $productIdentifiers;
    }
}
