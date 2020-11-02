<?php

namespace Akeneo\Pim\Permission\Component\Query;

use Akeneo\UserManagement\Component\Model\UserInterface;

interface ProductCategoryAccessQueryInterface
{
    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array;
}
