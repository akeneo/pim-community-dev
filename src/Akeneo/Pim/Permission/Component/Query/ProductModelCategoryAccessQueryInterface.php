<?php

namespace Akeneo\Pim\Permission\Component\Query;

use Symfony\Component\Security\Core\User\UserInterface;

interface ProductModelCategoryAccessQueryInterface
{
    public function getGrantedProductModelCodes(array $productModelCodes, UserInterface $user): array;
}
