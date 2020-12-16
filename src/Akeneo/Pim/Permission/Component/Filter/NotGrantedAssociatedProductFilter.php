<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter not granted associated product from product
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedAssociatedProductFilter implements NotGrantedDataFilterInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private ItemCategoryAccessQuery $productCategoryAccessQuery;
    private ItemCategoryAccessQuery $productModelCategoryAccessQuery;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->productCategoryAccessQuery = $productCategoryAccessQuery;
        $this->productModelCategoryAccessQuery = $productModelCategoryAccessQuery;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($entityWithAssociations)
    {
        if (!$entityWithAssociations instanceof EntityWithAssociationsInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithAssociations),
                EntityWithAssociationsInterface::class
            );
        }

        $entityWithAssociations->getAssociations();
        $filteredEntityWithAssociations = clone $entityWithAssociations;

        $user = $this->tokenStorage->getToken()->getUser();

        foreach ($filteredEntityWithAssociations->getAssociations() as $clonedAssociation) {
            $associationTypeCode = $clonedAssociation->getAssociationType()->getCode();
            $associatedProducts = clone $clonedAssociation->getProducts();
            $associatedProductModels = clone $clonedAssociation->getProductModels();

            $grantedProductIds = $this->productCategoryAccessQuery->getGrantedItemIds(
                $associatedProducts->toArray(),
                $user
            );

            foreach ($associatedProducts as $associatedProduct) {
                if (!isset($grantedProductIds[$associatedProduct->getId()])) {
                    $filteredEntityWithAssociations->removeAssociatedProduct(
                        $associatedProduct,
                        $associationTypeCode
                    );
                }
            }

            $grantedProductModelIds = $this->productModelCategoryAccessQuery->getGrantedItemIds(
                $associatedProductModels->toArray(),
                $user
            );
            foreach ($associatedProductModels as $associatedProductModel) {
                if (!isset($grantedProductModelIds[$associatedProductModel->getId()])) {
                    $filteredEntityWithAssociations->removeAssociatedProductModel(
                        $associatedProductModel,
                        $associationTypeCode
                    );
                }
            }
        }

        return $filteredEntityWithAssociations;
    }
}
