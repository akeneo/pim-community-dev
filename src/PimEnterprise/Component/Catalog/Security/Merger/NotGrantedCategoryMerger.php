<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Merge not granted categories with new categories. Example:
 * In database, your product "my_product" contains those categories:
 * {
 *    "categories": ["category_a", "category_b", "category_c"]
 * }
 *
 * But "category_a" is not viewable by the connected user.
 * That's means when he will get the product "my_product", the application will return:
 * {
 *    "categories": ["category_b", "category_c"]
 * }
 * (@see \PimEnterprise\Component\Catalog\Security\Filter\NotGrantedCategoryFilter)
 *
 * When user will update "my_product":
 * {
 *    "categories": ["category_c"]
 * }
 * we have to merge not granted data (here "category_a") before saving data in database.
 *
 * Finally, "my_product" will contain:
 * {
 *    "categories": ["category_a", "category_c"]
 * }
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedCategoryMerger implements NotGrantedDataMergerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var ItemCategoryRepositoryInterface */
    private $itemCategoryRepository;

    /**
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param ItemCategoryRepositoryInterface       $itemCategoryRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ItemCategoryRepositoryInterface $itemCategoryRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->itemCategoryRepository = $itemCategoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($product): void
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($product), ProductInterface::class);
        }

        if (null === $product->getId()) {
            return;
        }

        $categories = $this->itemCategoryRepository->findCategoriesItem($product);
        foreach ($categories as $category) {
            if (null !== $category && !$this->authorizationChecker->isGranted([Attributes::VIEW_ITEMS], $category)) {
                $product->addCategory($category);
            }
        }
    }
}
