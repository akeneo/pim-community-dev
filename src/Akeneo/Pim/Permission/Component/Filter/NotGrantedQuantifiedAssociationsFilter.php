<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Filter not granted associated product from product
 */
class NotGrantedQuantifiedAssociationsFilter implements NotGrantedDataFilterInterface
{
    /** @var ProductCategoryAccessQueryInterface */
    private $productCategoryAccessQuery;

    /** @var ProductModelCategoryAccessQueryInterface */
    private $productModelCategoryAccessQuery;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param ProductCategoryAccessQueryInterface $productCategoryAccessQuery
     * @param ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->productCategoryAccessQuery = $productCategoryAccessQuery;
        $this->productModelCategoryAccessQuery = $productModelCategoryAccessQuery;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($entityWithAssociations)
    {
        if (!$entityWithAssociations instanceof EntityWithQuantifiedAssociationsInterface) {
            throw InvalidObjectException::objectExpected(
                get_class($entityWithAssociations),
                EntityWithQuantifiedAssociationsInterface::class
            );
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $quantifiedAssociations = $entityWithAssociations->getQuantifiedAssociations();
        $quantifiedAssociationsProductIdentifiers = $quantifiedAssociations->getQuantifiedAssociationsProductIdentifiers();
        $quantifiedAssociationsProductModelCodes = $quantifiedAssociations->getQuantifiedAssociationsProductModelCodes();

        $grantedProductIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            $quantifiedAssociationsProductIdentifiers,
            $user
        );

        $grantedProductModelCodes = $this->productModelCategoryAccessQuery->getGrantedProductModelCodes(
            $quantifiedAssociationsProductModelCodes,
            $user
        );

        $entityWithAssociations->filterQuantifiedAssociations($grantedProductIdentifiers, $grantedProductModelCodes);

        return $entityWithAssociations;
    }
}
