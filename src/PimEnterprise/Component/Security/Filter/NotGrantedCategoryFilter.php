<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Filter;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter not granted categories from an entity with categories
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedCategoryFilter implements NotGrantedDataFilterInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($objectWithCategories)
    {
        if (!$objectWithCategories instanceof CategoryAwareInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($objectWithCategories),
                CategoryAwareInterface::class
            );
        }

        $objectWithCategories->getCategories();
        $filteredObjectWithCategories = clone $objectWithCategories;

        if ($objectWithCategories instanceof ProductInterface) {
            $categories = clone $objectWithCategories->getCategoriesForVariation();
        } else {
            $categories = clone $objectWithCategories->getCategories();
        }

        if (0 === $categories->count()) {
            return $filteredObjectWithCategories;
        }

        foreach ($categories as $index => $category) {
            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
                $categories->remove($index);
            }
        }

        $filteredObjectWithCategories->setCategories($categories);

        return $filteredObjectWithCategories;
    }
}
