<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
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

    /** @var array|object */
    protected $rawValues;

    /**
     * Not persisted. Loaded on the fly via the $rawValues.
     *
     * @var WriteValueCollection
     */
    protected $values;

    /** @var \DateTime $created */
    protected $created;

    /** @var \DateTime $updated */
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

    /** @var Collection $associations */
    protected $associations;

    /**
     * Create an instance of ProductModel.
     */
    public function __construct()
    {
        $this->values = new WriteValueCollection();
        $this->categories = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->productModels = new ArrayCollection();
        $this->associations = new ArrayCollection();
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
    public function getCode(): ?string
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
     *
     * @see ProductModel::setRawValues()
     */
    public function getRawValues(): array
    {
        if (is_object($this->rawValues)) {
            return [];
        }

        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     *
     * If the raw values are empty (i.e. []), Doctrine will save it as JSON as an array ([]), instead of an associative
     * array ({}). But we use JSON_MERGE to merge the values from product models and product in several queries, and the
     * SQL method JSON_MERGE([], {...}) does not have the same behavior than JSON_MERGE({}, {...}).
     * We have to trick a little bit before saving the value in database, by setting the raw value to an object, it
     * will be saved as {} and avoid issues with JSON_MERGE.
     */
    public function setRawValues(array $rawValues): ProductModelInterface
    {
        if ([] === $rawValues) {
            $rawValues = (object) [];
        }
        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): WriteValueCollection
    {
        $values = WriteValueCollection::fromCollection($this->values);

        return $this->getAllValues($this, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForVariation(): WriteValueCollection
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(WriteValueCollection $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null): ?ValueInterface
    {
        $result = $this->values->getByCodes($attributeCode, $scopeCode, $localeCode);
        if (null !== $result) {
            return $result;
        }

        if (null === $this->getParent()) {
            return null;
        }

        return $this->getParent()->getValue($attributeCode, $localeCode, $scopeCode);
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
    public function hasAttribute(string $attributeCode): bool
    {
        return in_array($attributeCode, $this->getValues()->getAttributeCodes(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes(): array
    {
        return $this->getValues()->getAttributeCodes();
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
    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
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
    public function addProduct(ProductInterface $product): ProductModelInterface
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
    public function getLabel(string $localeCode = null, string $scopeCode = null): string
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
        $scopeCode = $attributeAsLabel->isScopable() ? $scopeCode : null;
        $value = $this->getValue($attributeAsLabel->getCode(), $localeCode, $scopeCode);

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
     * {@inheritdoc}
     */
    public function getFamily(): ?FamilyInterface
    {
        return null !== $this->getFamilyVariant() ? $this->getFamilyVariant()->getFamily() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function addAssociation(AssociationInterface $association): EntityWithAssociationsInterface
    {
        if (!$this->associations->contains($association)) {
            $associationType = $association->getAssociationType();
            if (null !== $associationType && null !== $this->getAssociationForType($associationType)) {
                throw new \LogicException(
                    sprintf(
                        'Cannot add an association of type %s because the product model already has one',
                        $associationType->getCode()
                    )
                );
            }

            $this->associations->add($association);
            $association->setOwner($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAssociation(AssociationInterface $association): EntityWithAssociationsInterface
    {
        $this->associations->removeElement($association);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationForType(AssociationTypeInterface $type): ?AssociationInterface
    {
        return $this->getAssociationForTypeCode($type->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationForTypeCode($typeCode): ?AssociationInterface
    {
        foreach ($this->associations as $association) {
            if ($association->getAssociationType()->getCode() === $typeCode) {
                return $association;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociations(Collection $associations): EntityWithAssociationsInterface
    {
        $this->associations = $associations;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getLabel();
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param WriteValueCollection  $valueCollection
     *
     * @return WriteValueCollection
     */
    private function getAllValues(
        EntityWithFamilyVariantInterface $entity,
        WriteValueCollection $valueCollection
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

    /**
     * {@inheritdoc}
     */
    public function getAllAssociations()
    {
        $associations = new ArrayCollection($this->associations->toArray());
        $allAssociations = $this->getAncestryAssociations($this, $associations);

        return $allAssociations;
    }

    /**
     * @param ProductModelInterface $entity
     * @param Collection            $associationsCollection
     *
     * @return Collection
     */
    private function getAncestryAssociations(
        ProductModelInterface $entity,
        Collection $associationsCollection
    ): Collection {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $associationsCollection;
        }

        foreach ($parent->getAllAssociations() as $association) {
            $associationsCollection = $this->mergeAssociation($association, $associationsCollection);
        }

        return $associationsCollection;
    }

    private function mergeAssociation(
        AssociationInterface $association,
        Collection $associationsCollection
    ): Collection {
        $foundInCollection = null;
        foreach ($associationsCollection as $associationInCollection) {
            if ($associationInCollection->getAssociationType()->getCode() === $association->getAssociationType()->getCode()) {
                $foundInCollection = $associationInCollection;
            }
        }

        if (null !== $foundInCollection) {
            foreach ($association->getProducts() as $product) {
                $foundInCollection->addProduct($product);
            }
            foreach ($association->getProductModels() as $productModel) {
                $foundInCollection->addProductModel($productModel);
            }
            foreach ($association->getGroups() as $group) {
                $foundInCollection->addGroup($group);
            }
        }
        $associationsCollection->add($association);

        return $associationsCollection;
    }
}
