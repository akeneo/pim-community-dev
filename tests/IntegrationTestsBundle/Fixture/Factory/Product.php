<?php

namespace Akeneo\Test\IntegrationTestsBundle\Fixture\Factory;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create a product object with product builder, update its data with product update and validate this object
 */
final class Product
{
    /** @var ProductBuilderInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param ProductBuilderInterface $productModelFactory
     * @param ObjectUpdaterInterface  $productModelUpdater
     * @param ValidatorInterface      $validator
     */
    public function __construct(
        ProductBuilderInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        ValidatorInterface $validator
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->validator = $validator;
    }

    /**
     * TODO: all those method parameters must be replaced by object to remove default values and be more readable
     *
     * @param string $identifier
     * @param string $familyCode
     * @param array  $values
     * @param array  $categories
     * @param array  $groups
     * @param array  $associations
     * @param bool   $status
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductInterface
     */
    public function create(
        string $identifier,
        string $familyCode,
        array $values,
        array $categories = [],
        array $groups = [],
        array $associations = [],
        bool $status = true
    ): ProductInterface {
        $productModelData = [
            'values' => $values,
            'categories' => $categories,
            'associations' => $associations,
            'groups' => $groups,
            'enabled' => $status,
        ];

        $product = $this->productModelFactory->createProduct($identifier, $familyCode);
        $this->productModelUpdater->update($product, $productModelData);

        $errors = $this->validator->validate($product);
        if (0 < $errors->count()) {
            throw new \InvalidArgumentException(sprintf('The given product data are invalid: %s', $errors));
        }

        return $product;
    }
}