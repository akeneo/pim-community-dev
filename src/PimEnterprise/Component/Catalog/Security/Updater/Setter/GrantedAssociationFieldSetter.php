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
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Updater\Setter\AbstractFieldSetter;
use Pim\Component\Catalog\Updater\Setter\FieldSetterInterface;
use PimEnterprise\Component\Security\Attributes;
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

    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param FieldSetterInterface                  $categoryFieldSetter
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param array                                 $supportedFields
     * @param ProductModelRepositoryInterface|null  $productModelRepository
     *
     * @merge make $productModelRepository mandatory on master.
     */
    public function __construct(
        FieldSetterInterface $categoryFieldSetter,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productRepository,
        array $supportedFields,
        ProductModelRepositoryInterface $productModelRepository = null
    ) {
        $this->associationFieldSetter = $categoryFieldSetter;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->supportedFields = $supportedFields;
        $this->productModelRepository = $productModelRepository;
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
     * @param array $associatedProducts
     */
    private function checkAssociatedProducts(array $associatedProducts)
    {
        foreach ($associatedProducts as $associatedProductIdentifier) {
            $associatedProduct = $this->productRepository->findOneByIdentifier($associatedProductIdentifier);

            if (!$this->authorizationChecker->isGranted([Attributes::VIEW], $associatedProduct)) {
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
     * @param array $associatedProductModels
     */
    private function checkAssociatedProductModels(array $associatedProductModels)
    {
        // TODO: @merge to remove on master.
        if (null === $this->productModelRepository) {
            return;
        }

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
