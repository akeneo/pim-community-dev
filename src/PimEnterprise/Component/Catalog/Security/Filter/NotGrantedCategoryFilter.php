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

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter not granted categories from a product
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
    public function filter($product)
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($product), ProductInterface::class);
        }

        if (0 === $product->getCategories()->count()) {
            return $product;
        }

        $categoriesToRemove = [];
        foreach ($product->getCategories() as $index => $category) {
            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $category)) {
                $categoriesToRemove[$index] = $category;
            }
        }

        if (count($categoriesToRemove) === $product->getCategories()->count()) {
            throw new ResourceAccessDeniedException($product, sprintf(
                'You can neither view, nor update, nor delete the product "%s", as it is only categorized in categories on which you do not have a view permission.',
                $product->getIdentifier()
            ));
        }

        foreach ($categoriesToRemove as $index => $category) {
            $product->getCategories()->remove($index);
        }

        return $product;
    }
}
