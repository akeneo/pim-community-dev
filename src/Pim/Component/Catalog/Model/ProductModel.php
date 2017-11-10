<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModel implements ProductModelInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var array */
    protected $rawValues;

    /**
     * Not persisted. Loaded on the fly via the $rawValues.
     *
     * @var ValueCollectionInterface
     */
    protected $values;

    /** @var \Datetime $created */
    protected $created;

    /** @var \Datetime $updated */
    protected $updated;

    /** @var int */
    protected $root;

    /** @var int */
    protected $level;

    /** @var int */
    protected $left;

    /** @var int */
    protected $right;

    /** @var Collection $categories */
    protected $categories;

    /** @var Collection $categories */
    protected $products;

    /** @var ProductModelInterface */
    protected $parent;

    /** @var Collection */
    protected $productModels;

    /** @var FamilyVariantInterface */
    protected $familyVariant;

    /**
     * Create an instance of ProductModel.
     */
    public function __construct()
    {
        $this->values = new ValueCollection();
        $this->categories = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->productModels = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues(): array
    {
        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValues(array $rawValues): ProductModelInterface
    {
        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): ValueCollectionInterface
    {
        $values = ValueCollection::fromCollection($this->values);

        return $this->getAllValues($this, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForVariation(): ValueCollectionInterface
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(ValueCollectionInterface $values): ProductModelInterface
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null): ?ValueInterface
    {
        return $this->getValues()->getByCodes($attributeCode, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function addValue(ValueInterface $value): ProductModelInterface
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeValue(ValueInterface $value): ProductModelInterface
    {
        $this->values->remove($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->getValues()->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute): bool
    {
        return in_array($attribute, $this->getValues()->getAttributes(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes(): array
    {
        return $this->getValues()->getAttributesKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated($created): ProductModelInterface
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdated(): \DateTimeInterface
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdated($updated): ProductModelInterface
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories(): Collection
    {
        $categories = new ArrayCollection($this->categories->toArray());

        return $this->getAllCategories($this, $categories);
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(CategoryInterface $category): ProductModelInterface
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(CategoryInterface $category): ProductModelInterface
    {
        if (!$this->categories->contains($category) && !$this->hasAncestryCategory($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCodes(): array
    {
        $codes = $this->getCategories()->map(function (CategoryInterface $category) {
            return $category->getCode();
        })->toArray();

        sort($codes);

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(VariantProductInterface $product): ProductModelInterface
    {
        $product->setParent($this);
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct(ProductInterface $product): ProductModelInterface
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoot(int $root): ProductModelInterface
    {
        $this->root = $root;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot(): int
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot(): bool
    {
        return (null === $this->getParent());
    }

    /**
     * {@inheritdoc}
     */
    public function setLevel(int $level): ProductModelInterface
    {
        $this->level = $level;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function setLeft(int $left): ProductModelInterface
    {
        $this->left = $left;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeft(): int
    {
        return $this->left;
    }

    /**
     * {@inheritdoc}
     */
    public function setRight(int $right): ProductModelInterface
    {
        $this->right = $right;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRight(): int
    {
        return $this->right;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(ProductModelInterface $parent = null): void
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?ProductModelInterface
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function addProductModel(ProductModelInterface $child): ProductModelInterface
    {
        $child->setParent($this);
        if (!$this->productModels->contains($child)) {
            $this->productModels->add($child);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProductModel(ProductModelInterface $children): ProductModelInterface
    {
        $this->productModels->removeElement($children);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProductModels(): bool
    {
        return false === $this->getProductModels()->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductModels(): Collection
    {
        return $this->productModels;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyVariant(): ?FamilyVariantInterface
    {
        return $this->familyVariant;
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     */
    public function setFamilyVariant(FamilyVariantInterface $familyVariant): void
    {
        $this->familyVariant = $familyVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariationLevel(): int
    {
        $entity = $this;
        $level = 0;

        while (true) {
            $entity = $entity->getParent();
            if (null === $entity) {
                return $level;
            }

            $level++;
        }

        return $level;
    }

    /**
     * {@inheritdoc}
     */
    public function isRootProductModel(): bool
    {
        return null === $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(string $localeCode = null): string
    {
        $code = (string) $this->getCode();
        $familyVariant = $this->familyVariant;

        if (null === $familyVariant) {
            return $code;
        }

        $attributeAsLabel = $familyVariant->getFamily()->getAttributeAsLabel();

        if (null === $attributeAsLabel) {
            return $code;
        }

        $localeCode = $attributeAsLabel->isLocalizable() ? $localeCode : null;
        $value = $this->getValue($attributeAsLabel->getCode(), $localeCode);

        if (null === $value) {
            return $code;
        }

        $data = $value->getData();

        if (empty($data)) {
            return $code;
        }

        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(): ?ValueInterface
    {
        $attributeAsImage = $this->familyVariant->getFamily()->getAttributeAsImage();

        if (null === $attributeAsImage) {
            return null;
        }

        return $this->getValue($attributeAsImage->getCode());
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param ValueCollectionInterface         $valueCollection
     *
     * @return ValueCollectionInterface
     */
    private function getAllValues(
        EntityWithFamilyVariantInterface $entity,
        ValueCollectionInterface $valueCollection
    ) {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $valueCollection;
        }

        foreach ($parent->getValuesForVariation() as $value) {
            $valueCollection->add($value);
        }

        return $this->getAllValues($parent, $valueCollection);
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param Collection                       $categoryCollection
     *
     * @return Collection
     */
    private function getAllCategories(
        EntityWithFamilyVariantInterface $entity,
        Collection $categoryCollection
    ) {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $categoryCollection;
        }

        foreach ($parent->getCategories() as $category) {
            if (!$categoryCollection->contains($category)) {
                $categoryCollection->add($category);
            }
        }

        return $this->getAllCategories($parent, $categoryCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function getFamily(): ?FamilyInterface
    {
        return null !== $this->getFamilyVariant() ? $this->getFamilyVariant()->getFamily() : null;
    }

    /**
     * Does the ancestry of the entity already has the $category?
     *
     * @param CategoryInterface $category
     *
     * @return bool
     */
    private function hasAncestryCategory(CategoryInterface $category): bool
    {
        $parent = $this->getParent();
        if (null === $parent) {
            return false;
        }

        // no need recursion here as getCategories already look in the whole ancestry
        foreach ($parent->getCategories() as $ancestryCategory) {
            if ($ancestryCategory->getCode() === $category->getCode()) {
                return true;
            }
        }

        return false;
    }
}
