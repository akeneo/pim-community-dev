<?php

namespace Akeneo\Pim\Permission\Component\Query;

use Symfony\Component\Security\Core\User\UserInterface;

interface ProductCategoryAccessQueryInterface
{
    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array;
}
