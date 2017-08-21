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

namespace PimEnterprise\Component\Catalog\Security\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Apply permissions when updating the product.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedProductUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var ProductFilterInterface */
    private $productFieldFilter;

    /** @var ProductFilterInterface */
    private $productAssociationFilter;

    /** @var array */
    private $supportedFields;

    /** @var array */
    private $supportedAssociations;

    /**
     * @param ObjectUpdaterInterface        $productUpdater
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ProductFilterInterface        $productFieldFilter
     * @param ProductFilterInterface        $productAssociationFilter
     * @param array                         $supportedFields
     * @param array                         $supportedAssociations
     */
    public function __construct(
        ObjectUpdaterInterface $productUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductFilterInterface $productFieldFilter,
        ProductFilterInterface $productAssociationFilter,
        array $supportedFields,
        array $supportedAssociations
    ) {
        $this->productUpdater = $productUpdater;
        $this->authorizationChecker = $authorizationChecker;
        $this->productFieldFilter = $productFieldFilter;
        $this->productAssociationFilter = $productAssociationFilter;
        $this->supportedFields = $supportedFields;
        $this->supportedAssociations = $supportedAssociations;
    }

    /**
     * {@inheritdoc}
     */
    public function update($product, array $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                ProductInterface::class
            );
        }

        if (null !== $product->getId()) {
            $this->checkGrantedFieldsForProductDraft($product, $data);
        }

        $this->productUpdater->update($product, $data, $options);
        
        return $this;
    }

    /**
     * If product is a draft (that's means the user is not owner of the product but can edit it),
     * product's fields cannot be updated, but we allow their presence in the product to facilitate the update.
     * To know if a field has been updated, we call Filters
     * whose responsability is to compare submitted data with data in database and return only updated values.
     * If Filters return a non empty array, it means user tries to update a non granted field.
     *
     * @see \Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface
     *
     * @param ProductInterface $product
     * @param array            $data
     *
     * @throws InvalidArgumentException
     */
    private function checkGrantedFieldsForProductDraft(ProductInterface $product, array $data): void
    {
        $isOwner = $this->authorizationChecker->isGranted([Attributes::OWN], $product);
        $canEdit = $this->authorizationChecker->isGranted([Attributes::EDIT], $product);

        if (!$isOwner && $canEdit) {
            $fields = [];
            $associations = [];
            foreach ($data as $code => $values) {
                if (in_array($code, $this->supportedFields)) {
                    $fields[$code] = $values;
                } elseif (in_array($code, $this->supportedAssociations)) {
                    $associations[$code] = $values;
                }
            }

            $filteredFilters = !empty($fields) ? $this->productFieldFilter->filter($product, $fields) : [];
            $filteredAssociations = !empty($associations) ? $this->productAssociationFilter->filter($product, $associations) : [];
            if (!empty($filteredFilters) || !empty($filteredAssociations)) {
                throw new InvalidArgumentException(sprintf(
                    'You cannot update the field "%s". You should at least own this product to do it.',
                    implode(', ', array_keys(array_merge($filteredFilters, $filteredAssociations)))
                ));
            }
        }
    }
}
