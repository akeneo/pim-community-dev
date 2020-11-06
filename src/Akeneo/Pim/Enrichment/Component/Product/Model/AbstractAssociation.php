<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Abstract association entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAssociation implements AssociationInterface
{
    /** @var int|string */
    protected $id;

    /** @var AssociationTypeInterface */
    protected $associationType;

    /** @var EntityWithAssociationsInterface */
    protected $owner;

    /** @var Collection<ProductInterface> */
    protected $products;

    /** @var Collection<ProductModelInterface> */
    protected $productModels;

    /** @var Collection<GroupInterface> */
    protected $groups;

    /** @var array */
    protected $groupIds = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->productModels = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssociationType(AssociationTypeInterface $associationType): AssociationInterface
    {
        $this->associationType = $associationType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationType(): \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface
    {
        return $this->associationType;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(EntityWithAssociationsInterface $owner): AssociationInterface
    {
        if (!$this->owner) {
            $this->owner = $owner;
            $owner->addAssociation($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner(): \Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface
    {
        return $this->owner;
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts(array $products): AssociationInterface
    {
        $this->products = $products;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(ProductInterface $product): AssociationInterface
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProduct(ProductInterface $product): bool
    {
        return $this->products->contains($product);
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct(ProductInterface $product): AssociationInterface
    {
        $this->products->removeElement($product);

        return $this;
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
    public function addProductModel(ProductModelInterface $productModel): void
    {
        if (!$this->productModels->contains($productModel)) {
            $this->productModels->add($productModel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeProductModel(ProductModelInterface $productModel): void
    {
        $this->productModels->removeElement($productModel);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductModels(array $productModels): void
    {
        $this->productModels = $productModels;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups(array $groups): AssociationInterface
    {
        $this->groups = $groups;

        return $this;
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
    public function addGroup(GroupInterface $group): AssociationInterface
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group): AssociationInterface
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(): string
    {
        if (!$this->owner) {
            return null;
        }

        if (!$this->owner instanceof ProductInterface && !$this->owner instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Owner must be a product or a product model, instance of \'%s\' given',
                get_class($this->owner)
            ));
        }

        return $this->owner->getIdentifier() . '.' . $this->associationType->getCode();
    }
}
