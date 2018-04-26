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
use Pim\Component\Catalog\Model\AssociationAwareInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    /** @var CategoryAccessRepository */
    private $productCategoryAccessQuery;

    /** @var CategoryAccessRepository */
    private $productModelCategoryAccessQuery;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ItemCategoryAccessQuery|null  $productCategoryAccessQuery
     * @param ItemCategoryAccessQuery|null  $productModelCategoryAccessQuery
     * @param TokenStorageInterface|null    $tokenStorage
     *
     * @merge make $productCategoryAccessQuery mandatory on master.
     * @merge make $productModelCategoryAccessQuery mandatory on master.
     * @merge make $tokenStorage mandatory on master.
     * @merge remove $authorizationChecker on master.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ItemCategoryAccessQuery $productCategoryAccessQuery = null,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery = null,
        TokenStorageInterface $tokenStorage = null
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
        if (!$entityWithAssociations instanceof AssociationAwareInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($entityWithAssociations),
                ProductInterface::class
            );
        }

        $entityWithAssociations->getAssociations();
        $filteredEntityWithAssociations = clone $entityWithAssociations;
        $clonedAssociations = new ArrayCollection();

        if (null !== $this->tokenStorage) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        foreach ($filteredEntityWithAssociations->getAssociations() as $association) {
            $clonedAssociation = clone $association;
            $associatedProducts = clone $clonedAssociation->getProducts();
            $associatedProductModels = clone $clonedAssociation->getProductModels();

            if (null !== $this->productCategoryAccessQuery && null !== $this->productModelCategoryAccessQuery && null !== $this->tokenStorage) {
                $grantedProductIds = $this->productCategoryAccessQuery->getGrantedItemIds($associatedProducts->toArray(), $user);

                foreach ($associatedProducts as $associatedProduct) {
                    if (!isset($grantedProductIds[$associatedProduct->getId()])) {
                        $associatedProducts->removeElement($associatedProduct);
                    }
                }

                $grantedProductModelIds = $this->productModelCategoryAccessQuery->getGrantedItemIds($associatedProductModels->toArray(), $user);
                foreach ($associatedProductModels as $associatedProductModel) {
                    if (!isset($grantedProductModelIds[$associatedProductModel->getId()])) {
                        $associatedProductModels->removeElement($associatedProductModel);
                    }
                }
            } else { // TODO: @merge to remove on master.
                foreach ($associatedProducts as $associatedProduct) {
                    if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
                        $associatedProducts->removeElement($associatedProduct);
                    }
                }
                foreach ($associatedProductModels as $associatedProductModel) {
                    if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProductModel)) {
                        $associatedProductModels->removeElement($associatedProductModel);
                    }
                }
            }

            $clonedAssociation->setProducts($associatedProducts);
            $clonedAssociation->setProductModels($associatedProductModels);
            $clonedAssociations->add($clonedAssociation);
        }

        $filteredEntityWithAssociations->setAssociations($clonedAssociations);

        return $filteredEntityWithAssociations;
    }
}
