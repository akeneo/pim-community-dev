<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Check if product associated is at least "viewable" to be associated to a product

 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedAssociationFieldSetter implements FieldSetterInterface
{
    public function __construct(
        private FieldSetterInterface $associationFieldSetter,
        private ProductRepositoryInterface $productRepository,
        private array $supportedFields,
        private ProductModelRepositoryInterface $productModelRepository,
        private ItemCategoryAccessQuery $productCategoryAccessQuery,
        private ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($product, $field, $data, array $options = [])
    {
        $this->associationFieldSetter->setFieldData($product, $field, $data, $options);

        foreach ($data as $associations) {
            if (isset($associations['products']) && is_array($associations['products'])) {
                $this->checkAssociatedProducts($associations['products']);
            }

            if (isset($associations['product_uuids']) && is_array($associations['product_uuids'])) {
                $this->checkAssociatedProductUuids($associations['product_uuids']);
            }

            if (isset($associations['product_models']) && is_array($associations['product_models'])) {
                $this->checkAssociatedProductModels($associations['product_models']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field): bool
    {
        return in_array($field, $this->supportedFields, true);
    }

    private function checkAssociatedProductUuids(array $associatedProductUuids): void
    {
        $associatedProducts = $this->productRepository->findBy(['uuid' => $associatedProductUuids]);

        $user = $this->tokenStorage->getToken()->getUser();
        $grantedProductUuids = \array_flip(
            $this->productCategoryAccessQuery->getGrantedProductUuids($associatedProducts, $user)
        );

        foreach ($associatedProducts as $associatedProduct) {
            if (!isset($grantedProductUuids[$associatedProduct->getUuid()->toString()])) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product uuid',
                    'The product does not exist',
                    static::class,
                    $associatedProduct->getUuid()->toString()
                );
            }
        }
    }

    /**
     * @param array $associatedProductIdentifiers
     */
    private function checkAssociatedProducts(array $associatedProductIdentifiers): void
    {
        $associatedProducts = $this->productRepository->findBy(['identifier' => $associatedProductIdentifiers]);

        $user = $this->tokenStorage->getToken()->getUser();
        $grantedProductUuids = \array_flip(
            $this->productCategoryAccessQuery->getGrantedProductUuids($associatedProducts, $user)
        );

        foreach ($associatedProducts as $associatedProduct) {
            if (!isset($grantedProductUuids[$associatedProduct->getUuid()->toString()])) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product identifier',
                    'The product does not exist',
                    static::class,
                    $associatedProduct->getIdentifier()
                );
            }
        }
    }

    /**
     * @param array $associatedProductModels
     */
    private function checkAssociatedProductModels(array $associatedProductModels): void
    {
        $associatedProductModels = $this->productModelRepository->getItemsFromIdentifiers($associatedProductModels);

        $user = $this->tokenStorage->getToken()->getUser();
        $grantedProductModelIds = $this->productModelCategoryAccessQuery->getGrantedItemIds($associatedProductModels, $user);

        foreach ($associatedProductModels as $associatedProductModel) {
            if (!isset($grantedProductModelIds[$associatedProductModel->getId()])) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product model identifier',
                    'The product model does not exist',
                    static::class,
                    $associatedProductModel->getCode()
                );
            }
        }
    }
}
