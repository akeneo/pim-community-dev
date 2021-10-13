<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\FixturesLoader;

use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class CategoryPermissionsFixturesLoader
{
    private CategoryAccessManager $categoryAccessManager;
    private ProductCategoryRepositoryInterface $productCategoryRepository;

    public function __construct(
        ProductCategoryRepositoryInterface $productCategoryRepository,
        CategoryAccessManager $categoryAccessManager
    ) {
        $this->productCategoryRepository = $productCategoryRepository;
        $this->categoryAccessManager = $categoryAccessManager;
    }

    /**
     * @param string[] $categoryCodes
     */
    public function givenTheRightOnCategoryCodes(string $accessLevel, GroupInterface $userGroup, array $categoryCodes): void
    {
        foreach ($categoryCodes as $categoryCode) {
            $category = $this->productCategoryRepository->findOneByIdentifier($categoryCode);

            $this->categoryAccessManager->revokeAccess($category);
            $this->categoryAccessManager->grantAccess($category, $userGroup, $accessLevel);
        }
    }

    public function revokeCategoryPermissions(string $categoryCode): void
    {
        $category = $this->productCategoryRepository->findOneByIdentifier($categoryCode);
        $this->categoryAccessManager->revokeAccess($category);
    }
}
