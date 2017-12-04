<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Filter;

use Akeneo\Component\Classification\CategoryAwareInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
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

        if (0 === $objectWithCategories->getCategories()->count()) {
            return $objectWithCategories;
        }

        $categoriesToRemove = [];
        foreach ($objectWithCategories->getCategories() as $index => $category) {
            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
                $categoriesToRemove[$index] = $category;
            }
        }

        if (count($categoriesToRemove) === $objectWithCategories->getCategories()->count()) {
            if ($objectWithCategories instanceof ProductModelInterface) {
                throw new ResourceAccessDeniedException($objectWithCategories, sprintf(
                    'You can neither view, nor update, nor delete the product model "%s", as it is only categorized in categories on which you do not have a view permission.',
                    $objectWithCategories->getCode()
                ));
            }

            if ($objectWithCategories instanceof ProductInterface) {
                throw new ResourceAccessDeniedException($objectWithCategories, sprintf(
                    'You can neither view, nor update, nor delete the product "%s", as it is only categorized in categories on which you do not have a view permission.',
                    $objectWithCategories->getIdentifier()
                ));
            }

            throw new ResourceAccessDeniedException(
                $objectWithCategories,
                'You can neither view, nor update, nor delete this entity, as it is only categorized in categories on which you do not have a view permission.'
            );
        }

        foreach ($categoriesToRemove as $index => $category) {
            $objectWithCategories->getCategories()->remove($index);
        }

        return $objectWithCategories;
    }
}
