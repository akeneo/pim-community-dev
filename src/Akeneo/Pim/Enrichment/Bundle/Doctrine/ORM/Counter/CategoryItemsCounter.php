<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\Classification\Repository\ItemCategoryRepositoryInterface;

/**
 * Count item in a category
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryItemsCounter implements CategoryItemsCounterInterface
{
    /** @var ItemCategoryRepositoryInterface */
    protected $itemRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param ItemCategoryRepositoryInterface $itemRepository Item category repository
     * @param CategoryRepositoryInterface     $categoryRepo   Category repository
     */
    public function __construct(
        ItemCategoryRepositoryInterface $itemRepository,
        CategoryRepositoryInterface $categoryRepo
    ) {
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        $categoryIds = $inChildren
            ? $this->categoryRepository->getAllChildrenIds($category, $inProvided) : [$category->getId()];

        return $this->itemRepository->getItemsCountInCategory($categoryIds);
    }
}
