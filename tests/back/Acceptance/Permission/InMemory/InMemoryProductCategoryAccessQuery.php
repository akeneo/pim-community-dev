<?php

namespace AkeneoEnterprise\Test\Acceptance\Permission\InMemory;

use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class InMemoryProductCategoryAccessQuery implements ProductCategoryAccessQueryInterface
{
    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array
    {
        return $productIdentifiers;
    }
}
