<?php

namespace Akeneo\Pim\Permission\Component\Query;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\UuidInterface;

interface ProductCategoryAccessQueryInterface
{
    /**
     * The query is a union of products belonging to categories where the given user has access
     * and products without category.
     *
     * @param string[] $productIdentifiers
     * @return string[]
     */
    public function getGrantedProductIdentifiers(array $productIdentifiers, UserInterface $user): array;

    /**
     * @param UuidInterface[] $productUuids
     * @return UuidInterface[]
     */
    public function getGrantedProductUuids(array $productUuids, UserInterface $user): array;
}
