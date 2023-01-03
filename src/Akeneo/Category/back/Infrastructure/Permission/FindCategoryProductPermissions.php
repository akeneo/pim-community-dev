<?php

namespace AkeneoEnterprise\Category\Infrastructure\Permission;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\ServiceApi\Handler\CategoryAdditionalPropertiesFinder;
use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\GetCategoryProductPermissionsByCategoryId;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class FindCategoryProductPermissions implements CategoryAdditionalPropertiesFinder
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly GetCategoryProductPermissionsByCategoryId $getCategoryProductPermissionsByCategoryId,
    ) {
    }

    public function execute(Category $category): Category
    {
        $categoryPermissions = ($this->getCategoryProductPermissionsByCategoryId)($category->getId()->getValue());

        return Category::fromCategoryWithPermissions($category, $categoryPermissions);
    }

    public function isSupportedAdditionalProperties(): bool
    {
        return $this->securityFacade->isGranted('pimee_enrich_category_edit_permissions');
    }
}
