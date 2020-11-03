<?php

namespace Akeneo\Pim\Permission\Component\Query;

use Akeneo\UserManagement\Component\Model\UserInterface;

interface ProductModelCategoryAccessQueryInterface
{
    public function getGrantedProductModelCodes(array $productModelCodes, UserInterface $user): array;
}
