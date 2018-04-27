<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\Builder\EntityWithValue;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create a product object with product builder, update its data with product update and validate this object
 */
final class ProductBuilder
{
    /** @var Code */
    private $identifier;

    /** @var Code */
    private $family;

    /** @var Code */
    private $parent;

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
        $this->parent = Code::emptyCode();
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
     * @return ProductInterface
     *
     * @throws \InvalidArgumentException
     */
    public function build(): ProductInterface
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

        $errors = $this->validator->validate($product);
        if (0 < $errors->count()) {
            throw new \InvalidArgumentException(sprintf('The given product data are invalid: %s', $errors));
        }

        return $product;
    }

    /**
     * @param string $identifier
     *
     * @return ProductBuilder
     */
    public function withIdentifier(string $identifier): ProductBuilder
    {
        $this->identifier = Code::fromString($identifier);

        return $this;
    }

    /**
     * @param string $family
     *
     * @return ProductBuilder
     */
    public function withFamily(string $family): ProductBuilder
    {
        $this->family = Code::fromString($family);

        return $this;
    }

    /**
     * @param string $parent
     *
     * @return ProductBuilder
     */
    public function withParent(string $parent): ProductBuilder
    {
        $this->parent = Code::fromString($parent);

        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed  $data
     * @param string $locale
     * @param string $channel
     *
     * @return ProductBuilder
     */
    public function withValue(
        string $attribute,
        $data,
        string $locale = '',
        string $channel = ''
    ): ProductBuilder {
        $value = Value::withLocaleAndChannel($attribute, $data, $locale, $channel);
        $attribute = Code::fromString($attribute);

        $this->values->add($attribute, $value);

        return $this;
    }

    /**
     * @param array $categories
     *
     * @return ProductBuilder
     */
    public function withCategories(...$categories): ProductBuilder
    {
        $this->categories = ListOfCodes::fromArrayOfString($categories);

        return $this;
    }

    /**
     * @param array $groups
     *
     * @return ProductBuilder
     */
    public function withGroups(...$groups): ProductBuilder
    {
        $this->groups = ListOfCodes::fromArrayOfString($groups);

        return $this;
    }

    /**
     * @param string $type
     * @param array  ...$product
     *
     * @return ProductBuilder
     */
    public function withAssociations(string $type, ...$product): ProductBuilder
    {
        $this->associations = Association::create($type, $product);

        return $this;
    }

    /**
     * @param bool $status
     *
     * @return ProductBuilder
     */
    public function withStatus(bool $status): ProductBuilder
    {
        $this->status = Status::fromBoolean($status);

        return $this;
    }
}
