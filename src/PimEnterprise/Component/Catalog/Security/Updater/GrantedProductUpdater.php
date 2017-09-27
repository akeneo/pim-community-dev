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
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
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

    /** @var FilterInterface */
    private $productFieldFilter;

    /** @var FilterInterface */
    private $productAssociationFilter;

    /** @var array */
    private $supportedFields;

    /** @var FilterInterface */
    private $productFilter;

    /** @var array */
    private $supportedAssociations;

    /**
     * @param ObjectUpdaterInterface        $productUpdater
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FilterInterface               $productFieldFilter
     * @param FilterInterface               $productAssociationFilter
     * @param FilterInterface               $productFilter
     * @param array                         $supportedFields
     * @param array                         $supportedAssociations
     */
    public function __construct(
        ObjectUpdaterInterface $productUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        FilterInterface $productFieldFilter,
        FilterInterface $productAssociationFilter,
        FilterInterface $productFilter,
        array $supportedFields,
        array $supportedAssociations
    ) {
        $this->productUpdater = $productUpdater;
        $this->authorizationChecker = $authorizationChecker;
        $this->productFieldFilter = $productFieldFilter;
        $this->productAssociationFilter = $productAssociationFilter;
        $this->productFilter = $productFilter;
        $this->supportedFields = $supportedFields;
        $this->supportedAssociations = $supportedAssociations;
    }

    /**
     * {@inheritdoc}
     */
    public function update($product, array $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($product), ProductInterface::class);
        }

        // TODO: PIM-6564 will be done when we'll publish product model
        unset($data['variant_group']);
        if (null !== $product->getId()) {
            $this->checkGrantedFieldsForProductDraft($product, $data);
            $this->checkGrantedFieldsForViewableProduct($product, $data);
        }

        $this->productUpdater->update($product, $data, $options);

        return $this;
    }

    /**
     * If product is a draft (that's means the user is not owner of the product but can edit it),
     * product's fields cannot be updated, but we allow their presence in the product to facilitate the update.
     * To know if a field has been updated, we call Filters
     * whose responsibility is to compare submitted data with data in database and return only updated values.
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
            $fields = array_filter($data, function ($code) {
                return in_array($code, $this->supportedFields);
            }, ARRAY_FILTER_USE_KEY);
            $filteredProductFields = !empty($fields) ? $this->productFieldFilter->filter($product, $fields) : [];
            $updatedAssociations = $this->getUpdatedAssociations($product, $data);

            $updatedFields = array_keys(array_merge($filteredProductFields, $updatedAssociations));
            if (!empty($updatedFields)) {
                $message = count($updatedFields) > 1 ? 'following fields' : 'field';
                throw new InvalidArgumentException(sprintf(
                    'You cannot update the %s "%s". You should at least own this product to do it.',
                    $message,
                    implode(', ', $updatedFields)
                ));
            }
        }
    }

    /**
     * If user can only view the product, data cannot be updated
     * but we allow their presence in the product to facilitate the update (in particularly for import)
     *
     * @see \Pim\Component\Catalog\Comparator\Filter\ProductFilterInterface
     *
     * @param ProductInterface $product
     * @param array            $data
     *
     * @throws ResourceAccessDeniedException
     */
    private function checkGrantedFieldsForViewableProduct(ProductInterface $product, array $data): void
    {
        $canView = $this->authorizationChecker->isGranted([Attributes::VIEW], $product);
        $canEdit = $this->authorizationChecker->isGranted([Attributes::EDIT], $product);
        if ($canView && !$canEdit) {
            $fields = array_filter($data, function ($code) {
                return in_array($code, $this->supportedFields) || 'values' === $code;
            }, ARRAY_FILTER_USE_KEY);

            $updatedProduct = !empty($fields) ? $this->productFilter->filter($product, $fields) : [];
            $updatedAssociations = $this->getUpdatedAssociations($product, $data);

            if (!empty($updatedProduct) || !empty($updatedAssociations)) {
                throw new ResourceAccessDeniedException($product, sprintf(
                    'Product "%s" cannot be updated. It should be at least in an own category.',
                    $product->getIdentifier()
                ));
            }
        }
    }

    /**
     * Get associations which have been modified
     *
     * @param ProductInterface $product
     * @param array            $data
     *
     * @return array
     */
    private function getUpdatedAssociations(ProductInterface $product, array $data): array
    {
        $associations = array_filter($data, function ($code) {
            return in_array($code, $this->supportedAssociations);
        }, ARRAY_FILTER_USE_KEY);

        return !empty($associations) ? $this->productAssociationFilter->filter($product, $associations) : [];
    }
}
