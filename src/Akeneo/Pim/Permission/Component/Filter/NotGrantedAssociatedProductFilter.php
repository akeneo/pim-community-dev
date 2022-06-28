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
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Filter not granted associated product from product
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedAssociatedProductFilter implements NotGrantedDataFilterInterface
{
    public function __construct(
        private ItemCategoryAccessQuery $productCategoryAccessQuery,
        private ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        private TokenStorageInterface $tokenStorage
    ) {
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
            $associatedProductsAndPublishedProducts = clone $clonedAssociation->getProducts();
            $associatedProductModels = clone $clonedAssociation->getProductModels();

            $associatedProducts = $associatedProductsAndPublishedProducts->filter(
                fn ($entity): bool => !$entity instanceof PublishedProductInterface
            );
            if ($associatedProducts->count() > 0) {
                $grantedProductUuids = \array_flip($this->productCategoryAccessQuery->getGrantedProductUuids(
                    $associatedProducts->toArray(),
                    $user
                ));

                foreach ($associatedProducts as $associatedProduct) {
                    if (!isset($grantedProductUuids[$associatedProduct->getUuid()->toString()])) {
                        $filteredEntityWithAssociations->removeAssociatedProduct(
                            $associatedProduct,
                            $associationTypeCode
                        );
                    }
                }
            }

            $associatedPublishedProducts = $associatedProductsAndPublishedProducts->filter(
                fn ($entity): bool => $entity instanceof PublishedProductInterface
            );
            if ($associatedPublishedProducts->count() > 0) {
                $grantedPublishedProductIds = $this->productCategoryAccessQuery->getGrantedItemIds(
                    $associatedPublishedProducts->toArray(),
                    $user
                );
                foreach ($associatedPublishedProducts as $associatedPublishedProduct) {
                    if (!isset($grantedPublishedProductIds[$associatedPublishedProduct->getId()])) {
                        $filteredEntityWithAssociations->removeAssociatedProduct(
                            $associatedPublishedProduct,
                            $associationTypeCode
                        );
                    }
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
