<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;

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

    /** @var AbstractToProductAssociation[] */
    protected $toProductAssociations;

    /** @var AbstractToProductModelAssociation[] */
    protected $toProductModelAssociations;

    /** @var AbstractToGroupAssociation[] */
    protected $toGroupAssociations;

    /** @var array */
    protected $groupIds = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->toProductAssociations = new ArrayCollection();
        $this->toProductModelAssociations = new ArrayCollection();
        $this->toGroupAssociations = new ArrayCollection();
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
    public function setAssociationType(AssociationTypeInterface $associationType)
    {
        $this->associationType = $associationType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationType()
    {
        return $this->associationType;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(EntityWithAssociationsInterface $owner)
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
    public function getOwner()
    {
        return $this->owner;
    }

    private function createProductAssociationFromProduct(ProductInterface $product)
    {
        $newProductProductAssociation = $this->owner instanceof ProductInterface ? new ProductToProductAssociation() : new ProductModelToProductAssociation();
        $newProductProductAssociation->product = $product;
        $newProductProductAssociation->association = $this;
        $newProductProductAssociation->quantity = 1;

        return ($newProductProductAssociation);
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts($products)
    {
        foreach ( $this->toProductAssociations as $association) {
           $this->toProductAssociations->remove($association);
        }

        foreach($products as $product) {
            $this->toProductAssociations->add($this->createProductAssociationFromProduct($product));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts()
    {
        return $this->toProductAssociations->map(function ($productProductAssociation) {
            return $productProductAssociation->product;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(ProductInterface $product)
    {
        if (!$this->hasProduct($product)) {
            $this->toProductAssociations->add($this->createProductAssociationFromProduct($product));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProduct(ProductInterface $product)
    {
        return $this->toProductAssociations->exists(function ($key, $element) use ($product) {
            return $element->product->getId() === $product->getId();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct(ProductInterface $product)
    {
        foreach ($this->toProductAssociations as $association) {
            if ($association->getProduct()->getId() === $product->getId()) {
                $this->toProductAssociations->remove($association);
                break;
            }
        }

        return $this;
    }

    private function createProductModelAssociationFromProductModel(ProductModelInterface $productModel)
    {
        $newProductModelProductAssociation = $this->owner instanceof ProductInterface ? new ProductToProductModelAssociation() : new ProductModelToProductModelAssociation();
        $newProductModelProductAssociation->productModel = $productModel;
        $newProductModelProductAssociation->association = $this;
        $newProductModelProductAssociation->quantity = 1;

        return ($newProductModelProductAssociation);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductModels($productModels): void
    {
        $toProductModelAssociations = new ArrayCollection();
        foreach($productModels as $productModel) {
            $toProductModelAssociations->add($this->createProductModelAssociationFromProductModel($productModel));
        }

        $this->toProductModelAssociations = $toProductModelAssociations;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductModels()
    {
        return $this->toProductModelAssociations->map(function ($productModelProductAssociation) {
            return $productModelProductAssociation->productModel;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addProductModel(ProductModelInterface $productModel): void
    {
        if (!$this->hasProductModel($productModel)) {
            $this->toProductModelAssociations->add($this->createProductModelAssociationFromProductModel($productModel));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasProductModel(ProductModelInterface $productModel)
    {
        return $this->toProductModelAssociations->exists(function ($key, $element) use ($productModel) {
            return $element->productModel->getId() === $productModel->getId();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeProductModel(ProductModelInterface $productModel): void
    {
        $this->toProductModelAssociations->filter(function ($toProductModelAssociation) use ($productModel) {
            return $toProductModelAssociation->productModel->getId() !== $productModel->getId();
        });
    }
























    private function createGroupAssociationFromGroup(GroupInterface $group)
    {
        $newGroupProductAssociation = $this->owner instanceof ProductInterface ? new ProductToGroupAssociation() : new ProductModelToGroupAssociation();
        $newGroupProductAssociation->group = $group;
        $newGroupProductAssociation->association = $this;
        $newGroupProductAssociation->quantity = 1;

        return ($newGroupProductAssociation);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups($groups)
    {
        $newGroupProductAssociations = new ArrayCollection();
        foreach($groups as $group) {
            $newGroupProductAssociations->add($this->createGroupAssociationFromGroup($group));
        }

        $this->toGroupAssociations = $newGroupProductAssociations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->toGroupAssociations->map(function ($groupProductAssociation) {
            return $groupProductAssociation->group;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->hasGroup($group)) {
            $this->toGroupAssociations->add($this->createGroupAssociationFromGroup($group));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup(GroupInterface $group)
    {
        return $this->toGroupAssociations->exists(function ($key, $element) use ($group) {
            $element->group->getId() === $group->getId();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group)
    {
        $this->toGroupAssociations->filter(function ($groupProductAssociation) use ($group) {
            return $groupProductAssociation->group->getId() !== $group->getId();
        });

        return $this;
    }












    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->owner ? $this->owner->getIdentifier() . '.' . $this->associationType->getCode() : null;
    }
}
