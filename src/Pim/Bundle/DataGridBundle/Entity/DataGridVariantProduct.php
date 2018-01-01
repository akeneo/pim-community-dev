<?php

namespace Pim\Bundle\DataGridBundle\Entity;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * A datagrid product does not contain all the product values of the product, for performance concern.
 * Therefore, this product should not be saved to prevent any data loss.
 *
 * Its purpose is only for read usage.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataGridVariantProduct extends AbstractDataGridProduct implements VariantProductInterface
{
    /** @var ProductModelInterface $parent */
    protected $parent;

    /** @var FamilyVariantInterface */
    protected $familyVariant;

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
    public function getValuesForVariation(): ValueCollectionInterface
    {
        return $this->values;
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
    public function getCategories(): Collection
    {
        $categories = new ArrayCollection($this->categories->toArray());

        return $this->getAllCategories($this, $categories);
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
    public function addCategory(CategoryInterface $category)
    {
        if (!$this->categories->contains($category) && !$this->hasAncestryCategory($category)) {
            $this->categories->add($category);
        }

        return $this;
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
