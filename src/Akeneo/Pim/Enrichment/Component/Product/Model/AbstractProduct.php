<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\EventStore;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyAddedToProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyOfProductChanged;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyRemovedFromProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ParentOfProductAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ParentOfProductChanged;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductAddedToGroup;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCategorized;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductDisabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductEnabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductIdentifierUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductRemovedFromGroup;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductUncategorized;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueDeleted;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueEdited;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Abstract product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProduct implements ProductInterface
{
    /** @var int|string */
    protected $id;

    /** @var array */
    protected $rawValues;

    /** @var \DateTime $created */
    protected $created;

    /** @var \DateTime $updated */
    protected $updated;

    /**
     * Not persisted. Loaded on the fly via the $rawValues.
     *
     * @var WriteValueCollection
     */
    protected $values;

    /** @var FamilyInterface $family */
    protected $family;

    /** @var Collection $categories */
    protected $categories;

    /** @var array */
    public $categoryIds = [];

    /** @var bool $enabled */
    protected $enabled;

    /** @var Collection $groups */
    protected $groups;

    /** @var array */
    protected $groupIds = [];

    /** @var Collection $associations */
    protected $associations;

    /** @var Collection $completenesses */
    protected $completenesses;

    /** @var string */
    protected $identifier;

    /** @var ArrayCollection */
    protected $uniqueData;

    /** @var ProductModelInterface $parent */
    protected $parent;

    /** @var FamilyVariantInterface */
    protected $familyVariant;

    /** @var EventStore */
    protected $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new WriteValueCollection();
        $this->categories = new ArrayCollection();
        $this->completenesses = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->associations = new ArrayCollection();
        $this->uniqueData = new ArrayCollection();
        $this->initEvents();
        $this->setEnabled(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function addOrReplaceValue(ValueInterface $value): void
    {
        $formerValue = $this->values->getByCodes($value->getAttributeCode(), $value->getScopeCode(), $value->getLocaleCode());
        if (null !== $formerValue) {
            if ($formerValue->isEqual($value)) {
                return;
            }

            $this->values->remove($formerValue);
            $this->values->add($value);
            $this->events->add(new ValueEdited($value->getAttributeCode(), $value->getLocaleCode(), $value->getScopeCode()));
        } else {
            $this->values->add($value);
            $this->events->add(new ValueAdded($value->getAttributeCode(), $value->getLocaleCode(), $value->getScopeCode()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addValue(ValueInterface $value)
    {
        if (true === $this->values->add($value)) {
            $this->events->add(new ValueAdded($value->getAttributeCode(), $value->getLocaleCode(), $value->getScopeCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeValue(ValueInterface $value)
    {
        $isRemoved = $this->values->remove($value);
        if (true === $isRemoved) {
            $this->events->add(new ValueDeleted($value->getAttributeCode(), $value->getLocaleCode(), $value->getScopeCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes(): array
    {
        return $this->values->getAttributeCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null)
    {
        $value = $this->values->getByCodes($attributeCode, $scopeCode, $localeCode);
        if (null !== $value) {
            return $value;
        }

        if (null === $this->getParent()) {
            return null;
        }

        return $this->getParent()->getValue($attributeCode, $localeCode, $scopeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues()
    {
        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValues(array $rawValues)
    {
        $this->rawValues = $rawValues;

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
    public function getFamily(): ?FamilyInterface
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function setFamily(FamilyInterface $family = null)
    {
        $formerFamilyCode = null !== $this->family ? $this->family->getCode() : null;
        $newFamilyCode = null !== $family ? $family->getCode() : null;

        if (null === $formerFamilyCode && null !== $newFamilyCode) {
            $this->events->add(new FamilyAddedToProduct($newFamilyCode));
        } elseif (null !== $formerFamilyCode && null === $newFamilyCode) {
            $this->events->add(new FamilyRemovedFromProduct($formerFamilyCode));
        } elseif ($formerFamilyCode !== $newFamilyCode) {
            $this->events->add(new FamilyOfProductChanged($formerFamilyCode, $newFamilyCode));
        }

        $this->family = $family;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFamilyId()
    {
        return null !== $this->family ? $this->family->getId() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier(ValueInterface $identifier)
    {
        $previousIdentifier = $this->identifier;

        $this->identifier = $identifier->getData();

        $this->values->removeByAttributeCode($identifier->getAttributeCode());
        $this->values->add($identifier);

        if ($previousIdentifier !== null && $previousIdentifier !== $this->identifier) {
            $this->events->add(new ProductIdentifierUpdated($previousIdentifier));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): WriteValueCollection
    {
        $values = WriteValueCollection::fromCollection($this->values);

        if (!$this->isVariant()) {
            return $values;
        }

        return $this->getAllValues($this, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(WriteValueCollection $values)
    {
        $formerValues = null === $this->values ?
            new WriteValueCollection() :
            WriteValueCollection::fromCollection($this->values);

        foreach ($formerValues as $formerValue) {
            $matchingNewValue = $values->getByCodes(
                $formerValue->getAttributeCode(),
                $formerValue->getScopeCode(),
                $formerValue->getLocaleCode()
            );
            if (null === $matchingNewValue) {
                $this->events->add(
                    new ValueDeleted(
                        $formerValue->getAttributeCode(),
                        $formerValue->getLocaleCode(),
                        $formerValue->getScopeCode()
                    )
                );
            } elseif (!$formerValue->isEqual($matchingNewValue)) {
                $this->events->add(
                    new ValueEdited(
                        $formerValue->getAttributeCode(),
                        $formerValue->getLocaleCode(),
                        $formerValue->getScopeCode()
                    )
                );
            }
        }

        foreach ($values as $newValue) {
            if (null === $formerValues->getByCodes(
                    $newValue->getAttributeCode(),
                    $newValue->getScopeCode(),
                    $newValue->getLocaleCode()
                )) {
                $this->events->add(
                    new ValueAdded(
                        $newValue->getAttributeCode(),
                        $newValue->getLocaleCode(),
                        $newValue->getScopeCode()
                    )
                );
            }
        }

        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        if (null === $this->family) {
            return null;
        }

        $attributeAsImage = $this->family->getAttributeAsImage();

        if (null === $attributeAsImage) {
            return null;
        }

        return $this->getValue($attributeAsImage->getCode());
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel($locale = null, $scope = null)
    {
        $identifier = (string) $this->getIdentifier();

        if (null === $this->family) {
            return $identifier;
        }

        $attributeAsLabel = $this->family->getAttributeAsLabel();

        if (null === $attributeAsLabel) {
            return $identifier;
        }

        $locale = $attributeAsLabel->isLocalizable() ? $locale : null;
        $scope = $attributeAsLabel->isScopable() ? $scope : null;
        $value = $this->getValue($attributeAsLabel->getCode(), $locale, $scope);

        if (null === $value) {
            return $identifier;
        }

        $data = $value->getData();

        if (empty($data)) {
            return $identifier;
        }

        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        if (!$this->isVariant()) {
            return $this->categories;
        }

        $categories = new ArrayCollection($this->categories->toArray());

        return $this->getAllCategories($this, $categories);
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(BaseCategoryInterface $category)
    {
        if (!$this->categories->contains($category) && !$this->hasAncestryCategory($category)) {
            $this->categories->add($category);
            $this->events->add(new ProductCategorized($category->getCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategories(Collection $categories): void
    {
        $formerCategories = $this->getCategories();
        $categoriesToAdd = $categories->filter(
            function (CategoryInterface $category) use ($formerCategories) {
                return !$formerCategories->contains($category);
            }
        );
        $categoriesToRemove = $formerCategories->filter(
            function (Categoryinterface $category) use ($categories) {
                return !$categories->contains($category);
            }
        );

        foreach ($categoriesToRemove as $categoryToRemove) {
            $this->events->add(new ProductUncategorized($categoryToRemove->getCode()));
        }
        foreach ($categoriesToAdd as $categoryToAdd) {
            $this->events->add(new ProductCategorized($categoryToAdd->getCode()));
        }

        $this->categories = $categories;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(BaseCategoryInterface $category)
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            $this->events->add(new ProductUncategorized($category->getCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCodes()
    {
        $codes = [];
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }
        sort($codes);

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupCodes()
    {
        $codes = [];
        foreach ($this->getGroups() as $group) {
            $codes[] = $group->getCode();
        }
        sort($codes);

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups(Collection $groups): void
    {
        $formerGroups = $this->getGroups();
        $groupsToRemove = $formerGroups->filter(
            function (GroupInterface $formerGroup) use ($groups) {
                return !$groups->contains($formerGroup);
            }
        );
        $groupsToAdd = $groups->filter(
            function (GroupInterface $newGroup) use ($formerGroups) {
                return !$formerGroups->contains($newGroup);
            }
        );

        foreach ($groupsToRemove as $groupToRemove) {
            $this->events->add(new ProductRemovedFromGroup($groupToRemove->getCode()));
        }
        foreach ($groupsToAdd as $groupToAdd) {
            $this->events->add(new ProductAddedToGroup($groupToAdd->getCode()));
        }

        $this->groups = $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        if ($enabled === $this->enabled) {
            return;
        }

        $this->events->add((true === $enabled) ? new ProductEnabled() : new ProductDisabled());
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttributeInFamily(AttributeInterface $attribute)
    {
        return null !== $this->family && $this->family->getAttributes()->contains($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeRemovable(AttributeInterface $attribute)
    {
        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            return false;
        }

        if ($this->hasAttributeInFamily($attribute)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAttributeEditable(AttributeInterface $attribute)
    {
        if (!$this->hasAttributeInFamily($attribute)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $this->events->add(new ProductAddedToGroup($group->getCode()));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group)
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            $this->events->add(new ProductRemovedFromGroup($group->getCode()));
        }

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
     * {@inheritdoc}
     */
    public function addAssociation(AssociationInterface $association): EntityWithAssociationsInterface
    {
        if (!$this->associations->contains($association)) {
            $associationType = $association->getAssociationType();
            if (null !== $associationType && null !== $this->getAssociationForType($associationType)) {
                throw new \LogicException(
                    sprintf(
                        'Can not add an association of type %s because the product already has one',
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
    public function getAllAssociations()
    {
        $associations = new ArrayCollection($this->associations->toArray());
        $allAssociations = $this->getAncestryAssociations($this, $associations);

        return $allAssociations;
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
     * {@inheritdoc}
     */
    public function setCompletenesses(Collection $completenesses)
    {
        $this->completenesses = $completenesses;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletenesses()
    {
        return $this->completenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->getIdentifier();
    }

    /**
     * @return ArrayCollection
     */
    public function getUniqueData()
    {
        return $this->uniqueData;
    }

    /**
     * @param ProductUniqueDataInterface $uniqueData
     *
     * @return ProductInterface
     */
    public function addUniqueData(ProductUniqueDataInterface $uniqueData)
    {
        $this->uniqueData->add($uniqueData);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUniqueData(Collection $data): void
    {
        $this->uniqueData = $data;
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
    public function setParent(ProductModelInterface $parent = null): void
    {
        if (null === $this->parent && null !== $parent) {
            $this->events->add(new ParentOfProductAdded($parent->getCode()));
        } elseif (null !== $this->parent && null !== $parent) {
            $this->events->add(new ParentOfProductChanged($this->parent->getCode(), $parent->getCode()));
        }

        $this->parent = $parent;
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
        return $this->getParent()->getVariationLevel() + 1;
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
    public function getCategoriesForVariation(): Collection
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function isVariant(): bool
    {
        return null !== $this->getParent();
    }

    // TODO: this is only used for
    public function initEvents(): void
    {
        if (null === $this->events) {
            $this->events = new EventStore();
        }
    }

    public function popEvents(): array
    {
        // as the id is generated by the database, there is no other way to detect it as there is no function called
        // to create a product
        if (null === $this->id) {
            $this->events->add(new ProductCreated());
        }

        // TODO: fix this workaround; it is here because currently some business events can occur before the product gets an identifier:
        // - it is possible to instantiate a product without identifier
        // - it is possible to remove the identifier of a product
        // - when using the ProductUpdater with a newly created product, some properties are usually set before the identifier
        if (null === $this->identifier) {
            throw new \LogicException('Cannot pop events without identifier');
        }

        return $this->events->popEvents($this->identifier);
    }

    /**
     * @param EntityWithFamilyVariantInterface $entity
     * @param WriteValueCollection         $valueCollection
     *
     * @return WriteValueCollection
     */
    private function getAllValues(
        EntityWithFamilyVariantInterface $entity,
        WriteValueCollection $valueCollection
    ): WriteValueCollection {
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
    ): Collection {
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
     * @param EntityWithFamilyVariantInterface $entity
     * @param Collection                       $associationsCollection
     *
     * @return Collection
     */
    private function getAncestryAssociations(
        EntityWithFamilyVariantInterface $entity,
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

    /**
     * Merges one association in an association collection.
     * It first merge the product existing association
     * And then merges the association into the collection
     *
     * Merging an association means merging all the products, product models and groups
     * into the collection associations or adding it if it doesn't exist
     *
     * @param AssociationInterface $association
     * @param Collection           $associationsCollection
     *
     * @return Collection
     */
    private function mergeAssociation(
        AssociationInterface $association,
        Collection $associationsCollection
    ): Collection {
        $foundInCollection = null;
        foreach ($associationsCollection as $associationInCollection) {
            if ($associationInCollection->getAssociationType()->getCode() ===
                $association->getAssociationType()->getCode()) {
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

    public function __clone()
    {
        $this->events = new EventStore();
    }
}
