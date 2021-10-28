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

/**
 * Apply permissions when updating the product model.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class GrantedProductModelUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var FilterInterface */
    private $productModelFilter;

    /** @var array */
    private $supportedFields;

    public function __construct(
        ObjectUpdaterInterface $productModelUpdater,
        AuthorizationCheckerInterface $authorizationChecker,
        FilterInterface $productModelFilter,
        array $supportedFields
    ) {
        $this->productModelUpdater = $productModelUpdater;
        $this->authorizationChecker = $authorizationChecker;
        $this->productModelFilter = $productModelFilter;
        $this->supportedFields = $supportedFields;
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
        $canView = $this->authorizationChecker-->isGranted(Attributes::VIEW, $productModel);
        $canEdit = $this->authorizationChecker-->isGranted(Attributes::EDIT, $productModel);

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
}
