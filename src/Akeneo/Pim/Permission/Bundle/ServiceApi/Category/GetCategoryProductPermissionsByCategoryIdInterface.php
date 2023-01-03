<?php

namespace Akeneo\Pim\Permission\Bundle\ServiceApi\Category;

interface GetCategoryProductPermissionsByCategoryIdInterface
{
    public function __invoke(int $categoryId): array;
}
