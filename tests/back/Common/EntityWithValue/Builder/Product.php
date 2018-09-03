<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue\Builder;

use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\Common\EntityWithValue\Association;
use Akeneo\Test\Common\EntityWithValue\Code;
use Akeneo\Test\Common\EntityWithValue\ListOfCodes;
use Akeneo\Test\Common\EntityWithValue\Status;
use Akeneo\Test\Common\EntityWithValue\Value;
use Akeneo\Test\Common\EntityWithValue\ListOfValues;
use Doctrine\Common\Collections\Collection;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create a product object with product builder, update its data with product update and validate this object
 */
final class Product
{
    /** @var Code */
    private $identifier;

    /** @var Code */
    private $family;

    /** @var Collection */
    private $values;

    /** @var ListOfCodes */
    private $groups;

    /** @var ListOfCodes */
    private $associations;

    /** @var ListOfCodes */
    private $categories;

    /** @var ListOfCodes */
    private $status;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param ProductBuilderInterface $productBuilder
     * @param ObjectUpdaterInterface  $productUpdater
     * @param ValidatorInterface      $validator
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $validator
    ) {

        $this->identifier = Code::fromString('my-product');
        $this->family = Code::emptyCode();
        $this->values = ListOfValues::initialize();
        $this->categories = ListOfCodes::initialize();
        $this->associations = ListOfCodes::initialize();
        $this->groups = ListOfCodes::initialize();
        $this->status = Status::enabled();

        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->validator = $validator;
    }

    /**
     * Why do we need the $dataValidation param? We are using anemic model, that means we validate model before
     * its creation. Sometimes we need to create invalid model to check validation rules.
     *
     * @param bool $dataValidation
     *
     * @return ProductInterface
     *
     * @throws \InvalidArgumentException
     */
    public function build($dataValidation = true): ProductInterface
    {
        $productStandardFormat = [
            'values' => $this->values->toStandardFormat(),
            'categories' => $this->categories->toStandardFormat(),
            'associations' => $this->associations->toStandardFormat(),
            'groups' => $this->groups->toStandardFormat(),
            'enabled' => $this->status->toStandardFormat(),
        ];

        $product = $this->productBuilder->createProduct((string) $this->identifier, (string) $this->family);
        $this->productUpdater->update($product, $productStandardFormat);

        if ($dataValidation) {
            $errors = $this->validator->validate($product);
            if (0 < $errors->count()) {
                throw new \InvalidArgumentException(sprintf('The given product data are invalid: %s', $errors));
            }
        }

        return $product;
    }

    /**
     * @param string $identifier
     *
     * @return Product
     */
    public function withIdentifier(string $identifier): Product
    {
        $this->identifier = Code::fromString($identifier);

        return $this;
    }

    /**
     * @param string $family
     *
     * @return Product
     */
    public function withFamily(string $family): Product
    {
        $this->family = Code::fromString($family);

        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed  $data
     * @param string $locale
     * @param string $channel
     *
     * @return Product
     */
    public function withValue(
        string $attribute,
        $data,
        string $locale = '',
        string $channel = ''
    ): Product {
        $value = Value::withLocaleAndChannel($attribute, $data, $locale, $channel);
        $attribute = Code::fromString($attribute);

        $this->values->add($attribute, $value);

        return $this;
    }

    /**
     * @param array $categories
     *
     * @return Product
     */
    public function withCategories(...$categories): Product
    {
        $this->categories = ListOfCodes::fromArrayOfString($categories);

        return $this;
    }

    /**
     * @param array $groups
     *
     * @return Product
     */
    public function withGroups(...$groups): Product
    {
        $this->groups = ListOfCodes::fromArrayOfString($groups);

        return $this;
    }

    /**
     * @param string $type
     * @param array  ...$product
     *
     * @return Product
     */
    public function withAssociations(string $type, ...$product): Product
    {
        $this->associations = Association::create($type, $product);

        return $this;
    }

    /**
     * @param bool $status
     *
     * @return Product
     */
    public function withStatus(bool $status): Product
    {
        $this->status = Status::fromBoolean($status);

        return $this;
    }
}
