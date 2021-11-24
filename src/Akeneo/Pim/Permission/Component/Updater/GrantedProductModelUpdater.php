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

namespace Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Exception\ResourceAccessDeniedException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Apply permissions when updating the product model.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class GrantedProductModelUpdater implements ObjectUpdaterInterface
{
    private ObjectUpdaterInterface $productModelUpdater;
    private AuthorizationCheckerInterface $authorizationChecker;
    private FilterInterface $productModelFilter;
    private FilterInterface $productModelFieldFilter;
    private FilterInterface $productModelAssociationFilter;
    private array $supportedFields;
    private array $supportedAssociations;

    public function __construct(
        ObjectUpdaterInterface $productModelUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        FilterInterface $productModelFilter,
        FilterInterface $productModelFieldFilter,
        FilterInterface $productModelAssociationFilter,
        array $supportedFields,
        array $supportedAssociations
    ) {
        $this->productModelUpdater = $productModelUpdater;
        $this->authorizationChecker = $authorizationChecker;
        $this->productModelFilter = $productModelFilter;
        $this->productModelFieldFilter = $productModelFieldFilter;
        $this->productModelAssociationFilter = $productModelAssociationFilter;
        $this->supportedFields = $supportedFields;
        $this->supportedAssociations = $supportedAssociations;
    }

    /**
     * {@inheritdoc}
     */
    public function update($productModel, array $data, array $options = [])
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($productModel), ProductModelInterface::class);
        }

        // TODO: PIM-6564 will be done when we'll publish product model
        if (null !== $productModel->getId()) {
            $this->checkGrantedFieldsForViewableProductModel($productModel, $data);
            $this->checkGrantedFieldsForProductModelDraft($productModel, $data);
        }

        $this->productModelUpdater->update($productModel, $data, $options);

        return $this;
    }

    /**
     * If user can only view the product, data cannot be updated
     * but we allow their presence in the product model to facilitate the update (in particularly for import)
     *
     * @see \Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\ProductFilterInterface
     *
     * @param ProductModelInterface $productModel
     * @param array                 $data
     *
     * @throws ResourceAccessDeniedException
     */
    private function checkGrantedFieldsForViewableProductModel(ProductModelInterface $productModel, array $data): void
    {
        $canView = $this->authorizationChecker->isGranted(Attributes::VIEW, $productModel);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if ($canView && !$canEdit) {
            $fields = array_filter($data, function ($code) {
                return in_array($code, $this->supportedFields) || 'values' === $code;
            }, ARRAY_FILTER_USE_KEY);

            $updatedProductModel = !empty($fields) ? $this->productModelFilter->filter($productModel, $fields) : [];

            if (!empty($updatedProductModel)) {
                throw new ResourceAccessDeniedException($productModel, sprintf(
                    'Product model "%s" cannot be updated. You only have a view right on this product model.',
                    $productModel->getCode()
                ));
            }
        }
    }

    /**
     * If the product model is a draft (that's means the user is not owner of the product model but can edit it),
     * product model's fields cannot be updated, but we allow their presence in the product model to facilitate the update.
     * To know if a field has been updated, we call Filters
     * whose responsibility is to compare submitted data with data in database and return only updated values.
     * If Filters return a non empty array, it means user tries to update a non granted field.
     */
    private function checkGrantedFieldsForProductModelDraft(ProductModelInterface $productModel, array $data): void
    {
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productModel);
        $canEdit = $this->authorizationChecker->isGranted(Attributes::EDIT, $productModel);

        if (!$isOwner && $canEdit) {
            $fields = array_filter($data, function ($code) {
                return in_array($code, $this->supportedFields);
            }, ARRAY_FILTER_USE_KEY);
            $filteredProductModelFields = !empty($fields) ? $this->productModelFieldFilter->filter($productModel, $fields) : [];
            $updatedAssociations = $this->getUpdatedAssociations($productModel, $data);

            $updatedFields = array_keys(array_merge($filteredProductModelFields, $updatedAssociations));
            if (!empty($updatedFields)) {
                $message = count($updatedFields) > 1 ? 'following fields' : 'field';
                throw new InvalidArgumentException(sprintf(
                    'You cannot update the %s "%s". You should at least own this product model to do it.',
                    $message,
                    implode(', ', $updatedFields)
                ));
            }
        }
    }

    private function getUpdatedAssociations(ProductModelInterface $productModel, array $data): array
    {
        $associations = array_filter($data, function ($code) {
            return in_array($code, $this->supportedAssociations);
        }, ARRAY_FILTER_USE_KEY);

        return !empty($associations) ? $this->productModelAssociationFilter->filter($productModel, $associations) : [];
    }
}
