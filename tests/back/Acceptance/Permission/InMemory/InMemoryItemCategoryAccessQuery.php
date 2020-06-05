<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\Permission\InMemory;

use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Symfony\Component\Security\Core\User\UserInterface;

class InMemoryItemCategoryAccessQuery extends ItemCategoryAccessQuery
{
    public function __construct()
    {
    }

    public function getGrantedItemIds(array $items, UserInterface $user): array
    {
        return array_flip(array_map(function ($item) {
            return $item->getId();
        }, $items));
    }
}
