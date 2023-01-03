<?php

namespace Akeneo\Pim\Permission\Bundle\Category;

interface GetCategoryProductPermissions
{
    public function __invoke(int $categoryId): array;
}
