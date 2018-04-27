<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\Builder\EntityWithValue;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Test\Common\Builder\EntityBuilder;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Create a product model object with product factory, update its data with product model update and validate
 * this object
 */
final class ProductModelBuilder
{
    /** @var Code */
    private $code;

    /** @var Code */
    private $familyVariant;

    /** @var Code */
    private $parent;

    /** @var Value */
    private $values;

    /** @var ListOfCodes */
    private $categories;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var EntityBuilder $entityBuilder */
    private $entityBuilder;

    /**
     * @param EntityBuilder                         $entityBuilder
     * @param IdentifiableObjectRepositoryInterface $productModelRepository ,
     */
    public function __construct(
        EntityBuilder $entityBuilder,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->code = Code::fromString('my-product-model');
        $this->familyVariant = Code::emptyCode();
        $this->values = ListOfValues::initialize();
        $this->categories = ListOfCodes::initialize();
        $this->parent = Code::emptyCode();

        $this->entityBuilder = $entityBuilder;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * @return ProductModelInterface
     *
     * @throws \InvalidArgumentException
     */
    public function build(): ProductModelInterface
    {
        if (!$this->parent->empty()) {
            if (null === $this->productModelRepository->findOneByIdentifier((string) $this->parent)) {
                throw new \InvalidArgumentException(sprintf('The parent "%s" does not exist.'));
            }
        }

        $productModelStandardFormat = [
            'code' => (string) $this->code,
            'family_variant' => (string) $this->familyVariant,
            'parent' => (string) $this->parent,
            'values' => $this->values->toStandardFormat(),
            'categories' => $this->categories->toStandardFormat(),
        ];

        /** @var ProductModelInterface $productModel */
        $productModel = $this->entityBuilder->build($productModelStandardFormat);

        return $productModel;
    }

    /**
     * @param string $code
     *
     * @return ProductModelBuilder
     */
    public function withCode(string $code): ProductModelBuilder
    {
        $this->code = Code::fromString($code);

        return $this;
    }

    /**
     * @param string $familyVariant
     *
     * @return ProductModelBuilder
     */
    public function withFamilyVariant(string $familyVariant): ProductModelBuilder
    {
        $this->familyVariant = Code::fromString($familyVariant);

        return $this;
    }

    /**
     * @param string $parent
     *
     * @return ProductModelBuilder
     */
    public function withParent(string $parent): ProductModelBuilder
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
     * @return ProductModelBuilder
     */
    public function withValue(
        string $attribute,
        $data,
        string $locale = 'all-locales',
        string $channel = 'all-channels'
    ): ProductModelBuilder {
        $value = Value::withLocaleAndChannel($attribute, $data, $locale, $channel);
        $attribute = Code::fromString($attribute);

        $this->values->add($attribute, $value);

        return $this;
    }

    /**
     * @param array $categories
     *
     * @return ProductModelBuilder
     */
    public function withCategories(...$categories): ProductModelBuilder
    {
        $this->categories = ListOfCodes::fromArrayOfString($categories);

        return $this;
    }
}
