<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Fixture\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create a product model object with product factory, update its data with product model update and validate
 * this object
 */
final class ProductModel
{
    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param SimpleFactoryInterface                $productModelFactory
     * @param ObjectUpdaterInterface                $productUpdater
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productUpdater,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ValidatorInterface $validator
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->productUpdater = $productUpdater;
        $this->productModelRepository = $productModelRepository;
        $this->validator = $validator;
    }

    /**
     * TODO: all those method parameters must be replaced by object to remove default values and be more readable
     *
     * @param string $code
     * @param string $familyVariantCode
     * @param array  $values
     * @param string $parentCode
     * @param array  $categories
     * @param array  $associations
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductModelInterface
     */
    public function create(
        string $code,
        string $familyVariantCode,
        array $values,
        string $parentCode = '',
        array $categories = [],
        array $associations = []
    ): ProductModelInterface {
        $productData = [
            'code' => $code,
            'family_variant' => $familyVariantCode,
            'values' => $values,
            'categories' => $categories,
        ];

        if (!empty($associations)) {
            $productModel['associations'] = $associations;
        }

        /** @var ProductModelInterface $productModel */
        $productModel = $this->productModelFactory->create();
        $this->productUpdater->update($productModel, $productData);

        if ($parent = $this->productModelRepository->findOneByIdentifier($parentCode)) {
            $productModel->setParent($parent);
        }

        $errors = $this->validator->validate($productModel);
        if (0 < $errors->count()) {
            throw new \InvalidArgumentException(sprintf('The given product data are invalid: %s', $errors));
        }

        return $productModel;
    }
}
