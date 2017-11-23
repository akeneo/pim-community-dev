<?php

declare(strict_types=1);

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
    private $productFactory;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param ProductBuilderInterface $productFactory
     * @param ObjectUpdaterInterface  $productUpdater
     * @param ValidatorInterface      $validator
     */
    public function __construct(
        ProductBuilderInterface $productFactory,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $validator
    ) {
        $this->productFactory = $productFactory;
        $this->productUpdater = $productUpdater;
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

        $product = $this->productFactory->createProduct($identifier, $familyCode);
        $this->productUpdater->update($product, $productModelData);

        $errors = $this->validator->validate($product);
        if (0 < $errors->count()) {
            throw new \InvalidArgumentException(sprintf('The given product data are invalid: %s', $errors));
        }

        return $product;
    }
}
