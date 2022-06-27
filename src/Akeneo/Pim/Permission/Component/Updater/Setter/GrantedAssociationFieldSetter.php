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

use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AbstractFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Query\ItemCategoryAccessQuery;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
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
     * @param CursorableRepositoryInterface      $productModelRepository
     * @param ItemCategoryAccessQuery            $productCategoryAccessQuery
     * @param ItemCategoryAccessQuery            $productModelCategoryAccessQuery
     * @param TokenStorageInterface              $tokenStorage
     */
    public function __construct(
        FieldSetterInterface $associationFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        CursorableRepositoryInterface $productRepository,
        array $supportedFields,
        CursorableRepositoryInterface $productModelRepository,
        ItemCategoryAccessQuery $productCategoryAccessQuery,
        ItemCategoryAccessQuery $productModelCategoryAccessQuery,
        TokenStorageInterface $tokenStorage
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
        $associatedProducts = $this->productRepository->getItemsFromIdentifiers($associatedProductIdentifiers);

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
    private function checkAssociatedProductModels(array $associatedProductModels)
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
