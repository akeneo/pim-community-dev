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

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Check if product associated is at least "viewable" to be associated to a product

 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedAssociationFieldSetter implements FieldSetterInterface
{
    public function __construct(
        private FieldSetterInterface $associationFieldSetter,
        private array $supportedFields,
        private ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        private ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        private TokenStorageInterface $tokenStorage,
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
        $user = $this->tokenStorage->getToken()->getUser();

        $uuids = array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $associatedProductUuids);
        $grantedProductUuids = array_map(
            fn (UuidInterface $uuid) => $uuid->toString(),
            $this->productCategoryAccessQuery->getGrantedProductUuids($uuids, $user)
        );

        foreach ($uuids as $associatedProductUuid) {
            if (!\in_array($associatedProductUuid, $grantedProductUuids)) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product uuid',
                    'The product does not exist',
                    static::class,
                    $associatedProductUuid->toString()
                );
            }
        }
    }

    /**
     * @param array $associatedProductIdentifiers
     */
    private function checkAssociatedProducts(array $associatedProductIdentifiers): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $grantedProductIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            $associatedProductIdentifiers,
            $user
        );

        foreach ($associatedProductIdentifiers as $associatedProductIdentifier) {
            if (!\in_array($associatedProductIdentifier, $grantedProductIdentifiers)) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product identifier',
                    'The product does not exist',
                    static::class,
                    $associatedProductIdentifier
                );
            }
        }
    }

    /**
     * @param array $associatedProductModelCodes
     */
    private function checkAssociatedProductModels(array $associatedProductModelCodes): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $grantedProductModelCodes = $this->productModelCategoryAccessQuery->getGrantedProductModelCodes(
            $associatedProductModelCodes,
            $user
        );

        foreach ($associatedProductModelCodes as $associatedProductModelCode) {
            if (!\in_array($associatedProductModelCode, $grantedProductModelCodes)) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'associations',
                    'product model identifier',
                    'The product model does not exist',
                    static::class,
                    $associatedProductModelCode
                );
            }
        }
    }
}
