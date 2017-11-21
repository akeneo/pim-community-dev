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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter not granted associated product from product
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedAssociatedProductFilter implements NotGrantedDataFilterInterface
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
        if (!$objectWithCategories instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($objectWithCategories), ProductInterface::class);
        }

        $clonedAssos = new ArrayCollection();

        foreach ($objectWithCategories->getAssociations() as $association) {
            $clonedAsso = clone $association;
            $associatedProducts = clone $clonedAsso->getProducts();

            foreach ($associatedProducts as $associatedProduct) {
                if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
                    $associatedProducts->removeElement($associatedProduct);
                }
            }

            $clonedAsso->setProducts($associatedProducts);
            $clonedAssos->add($clonedAsso);
        }

        $objectWithCategories->setAssociations($clonedAssos);

        return $objectWithCategories;
    }
}
