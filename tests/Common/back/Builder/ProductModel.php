<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue\Builder;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Code;
use Akeneo\Test\Common\EntityWithValue\ListOfCodes;
use Akeneo\Test\Common\EntityWithValue\ListOfValues;
use Akeneo\Test\Common\EntityWithValue\Value;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * Create a product model object with product factory, update its data with product model update and validate
 * this object
 */
final class ProductModel
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
        $productModelStandardFormat = [
            'code' => (string) $this->code,
            'family_variant' => (string) $this->familyVariant,
            'values' => $this->values->toStandardFormat(),
            'categories' => $this->categories->toStandardFormat(),
        ];

        /** @var ProductModelInterface $productModel */
        $productModel = $this->entityBuilder->build($productModelStandardFormat);

        if (!$this->parent->empty()) {
            if (null === $parent = $this->productModelRepository->findOneByIdentifier((string) $this->parent)) {
                throw new \InvalidArgumentException(sprintf('The parent "%s" does not exist.'));
            }

            $productModel->setParent($parent);
        }

        return $productModel;
    }

    /**
     * @param string $code
     *
     * @return ProductModel
     */
    public function withCode(string $code): ProductModel
    {
        $this->code = Code::fromString($code);

        return $this;
    }

    /**
     * @param string $familyVariant
     *
     * @return ProductModel
     */
    public function withFamilyVariant(string $familyVariant): ProductModel
    {
        $this->familyVariant = Code::fromString($familyVariant);

        return $this;
    }

    /**
     * @param string $parent
     *
     * @return ProductModel
     */
    public function withParent(string $parent): ProductModel
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
     * @return ProductModel
     */
    public function withValue(
        string $attribute,
        $data,
        string $locale = 'all-locales',
        string $channel = 'all-channels'
    ): ProductModel {
        $value = Value::withLocaleAndChannel($attribute, $data, $locale, $channel);
        $attribute = Code::fromString($attribute);

        $this->values->add($attribute, $value);

        return $this;
    }

    /**
     * @param array $categories
     *
     * @return ProductModel
     */
    public function withCategories(...$categories): ProductModel
    {
        $this->categories = ListOfCodes::fromArrayOfString($categories);

        return $this;
    }
}
