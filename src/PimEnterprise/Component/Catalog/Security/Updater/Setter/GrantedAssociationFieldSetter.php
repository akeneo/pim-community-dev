<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Query\ItemCategoryAccessQuery;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Check if product associated is at least "viewable" to be associated to a product

 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class GrantedAssociationFieldSetter extends AbstractFieldSetter implements FieldSetterInterface
{
    /** @var FieldSetterInterface */
    private $associationFieldSetter;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var CursorableRepositoryInterface */
    private $productRepository;

    /** @var CursorableRepositoryInterface */
    private $productModelRepository;

    /** @var null|ItemCategoryAccessQuery */
    private $productCategoryAccessQuery;

    /** @var null|ItemCategoryAccessQuery */
    private $productModelCategoryAccessQuery;

    /** @var null|TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param FieldSetterInterface               $associationFieldSetter
     * @param AuthorizationCheckerInterface      $authorizationChecker
     * @param CursorableRepositoryInterface      $productRepository
     * @param array                              $supportedFields
     * @param CursorableRepositoryInterface|null $productModelRepository
     * @param ItemCategoryAccessQuery|null       $productCategoryAccessQuery
     * @param ItemCategoryAccessQuery|null       $productModelCategoryAccessQuery
     * @param TokenStorageInterface|null         $tokenStorage
     *
     * @merge make $productModelRepository mandatory on master.
     * @merge make $productCategoryAccessQuery mandatory on master.
     * @merge make $productModelCategoryAccessQuery mandatory on master.
     * @merge make $tokenStorage mandatory on master.
     * @merge remove $authorizationChecker on master.
     */
    public function __construct(
        FieldSetterInterface $associationFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        CursorableRepositoryInterface $productRepository,
        array $supportedFields,
        CursorableRepositoryInterface $productModelRepository = null,
        ItemCategoryAccessQuery $productCategoryAccessQuery = null,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery = null,
        TokenStorageInterface $tokenStorage = null
    ) {
        $this->associationFieldSetter = $associationFieldSetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->supportedFields = $supportedFields;
        $this->productModelRepository = $productModelRepository;
        $this->productCategoryAccessQuery = $productCategoryAccessQuery;
        $this->productModelCategoryAccessQuery = $productModelCategoryAccessQuery;
        $this->tokenStorage = $tokenStorage;
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

            if (isset($associations['product_models']) && is_array($associations['product_models'])) {
                $this->checkAssociatedProductModels($associations['product_models']);
            }
        }
    }

    /**
     * @param array $associatedProductIdentifiers
     */
    private function checkAssociatedProducts(array $associatedProductIdentifiers)
    {
        if (null !== $this->productCategoryAccessQuery && null !== $this->tokenStorage) {
            $associatedProducts = $this->productRepository->getItemsFromIdentifiers($associatedProductIdentifiers);

            $user = $this->tokenStorage->getToken()->getUser();
            $grantedProductIds = $this->productCategoryAccessQuery->getGrantedItemIds($associatedProducts, $user);

            foreach ($associatedProducts as $associatedProduct) {
                if (!isset($grantedProductIds[$associatedProduct->getId()])) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'associations',
                        'product identifier',
                        'The product does not exist',
                        static::class,
                        $associatedProduct->getIdentifier()
                    );
                }
            }
        } else { // TODO: @merge to remove on master.
            foreach ($associatedProductIdentifiers as $associatedProductIdentifier) {
                $associatedProduct = $this->productRepository->findOneByIdentifier($associatedProductIdentifier);

                if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'associations',
                        'product identifier',
                        'The product does not exist',
                        static::class,
                        $associatedProduct
                    );
                }
            }
        }
    }

    /**
     * @param array $associatedProductModels
     */
    private function checkAssociatedProductModels(array $associatedProductModels)
    {
        if (null !== $this->productModelCategoryAccessQuery && null !== $this->tokenStorage) {
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
        } else { // TODO: @merge to remove on master.
            foreach ($associatedProductModels as $associatedProductModelCode) {
                $associatedProductModel = $this->productModelRepository->findOneByIdentifier($associatedProductModelCode);

                if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProductModel)) {
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
}
