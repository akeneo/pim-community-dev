<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotGrantedQuantifiedAssociationsMerger implements NotGrantedDataMergerInterface
{
    /** @var FieldSetterInterface */
    private $fieldSetter;

    /** @var ProductCategoryAccessQueryInterface */
    private $productCategoryAccessQuery;

    /** @var ProductModelCategoryAccessQueryInterface */
    private $productModelCategoryAccessQuery;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        FieldSetterInterface $fieldSetter,
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
    ) {
        $this->fieldSetter = $fieldSetter;
        $this->productCategoryAccessQuery = $productCategoryAccessQuery;
        $this->productModelCategoryAccessQuery = $productModelCategoryAccessQuery;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredProduct, $fullProduct = null)
    {
        if (!$filteredProduct instanceof EntityWithQuantifiedAssociationsInterface) {
            throw InvalidObjectException::objectExpected(
                get_class($filteredProduct),
                EntityWithQuantifiedAssociationsInterface::class
            );
        }

        if (null === $fullProduct) {
            return $filteredProduct;
        }

        if (!$fullProduct instanceof EntityWithQuantifiedAssociationsInterface) {
            throw InvalidObjectException::objectExpected(
                get_class($fullProduct),
                EntityWithQuantifiedAssociationsInterface::class
            );
        }

        $fullProductQuantifiedAssociationsNotGranted = $this->filterQuantifiedAssociationsNotGranted(
            $fullProduct->getQuantifiedAssociations()
        );

        $mergedQuantifiedAssociations = $fullProductQuantifiedAssociationsNotGranted->merge(
            $filteredProduct->getQuantifiedAssociations()
        );

        $this->fieldSetter->setFieldData(
            $fullProduct,
            'quantified_associations',
            $mergedQuantifiedAssociations->normalize()
        );

        return $fullProduct;
    }

    private function filterQuantifiedAssociationsNotGranted(
        QuantifiedAssociationCollection $quantifiedAssociations
    ): QuantifiedAssociationCollection {
        $quantifiedAssociationsProductIdentifiers = $quantifiedAssociations->getQuantifiedAssociationsProductIdentifiers();
        $quantifiedAssociationsProductModelCodes = $quantifiedAssociations->getQuantifiedAssociationsProductModelCodes();
        $user = $this->tokenStorage->getToken()->getUser();

        $grantedProductIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            $quantifiedAssociationsProductIdentifiers,
            $user
        );

        $grantedProductModelCodes = $this->productModelCategoryAccessQuery->getGrantedProductModelCodes(
            $quantifiedAssociationsProductModelCodes,
            $user
        );

        $notGrantedProductIdentifiers = array_diff($quantifiedAssociationsProductIdentifiers, $grantedProductIdentifiers);
        $notGrantedProductModelCodes = array_diff($quantifiedAssociationsProductModelCodes, $grantedProductModelCodes);

        $filteredQuantifiedAssociations = $quantifiedAssociations->filterProductIdentifiers($notGrantedProductIdentifiers);

        return $filteredQuantifiedAssociations->filterProductModelCodes($notGrantedProductModelCodes);
    }
}
