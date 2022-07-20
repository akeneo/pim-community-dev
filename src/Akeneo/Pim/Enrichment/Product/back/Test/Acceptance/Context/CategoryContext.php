<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Akeneo\Test\Category\Acceptance\InMemory\InMemoryGetOwnedCategories;
use Behat\Behat\Context\Context;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryContext implements Context
{
    public function __construct(
        private InMemoryCategoryRepository $categoryRepository,
        private InMemoryGetOwnedCategories $getOwnedCategories
    ) {
    }

    /**
     * @Given the :categoryCode category
     */
    public function theCategory(string $categoryCode): void
    {
        $category = new Category();
        $category->setCode($categoryCode);

        $this->categoryRepository->save($category);
    }

    /**
     * @Given the :groupName user group is owner on the :categoryCode category
     */
    public function theUserGroupIsOwnerOnTheCategory(string $groupName, string $categoryCode): void
    {
        $this->getOwnedCategories->addOwnedCategoryCode($groupName, $categoryCode);
    }
}
