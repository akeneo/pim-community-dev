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

    /** @var AbstractProductProductAssociation[] */
    protected $productProductAssociations;

    /** @var AbstractProductModelProductAssociation[] */
    protected $productModelProductAssociations;

    /** @var AbstractGroupProductAssociation[] */
    protected $groupProductAssociations;

    /** @var array */
    protected $groupIds = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productProductAssociations = new ArrayCollection();
        $this->productModelProductAssociations = new ArrayCollection();
        $this->groupProductAssociations = new ArrayCollection();
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
        $newProductProductAssociation = $this->owner instanceof ProductInterface ? new ProductProductAssociation() : new ProductProductModelAssociation();
        $newProductProductAssociation->product = $product;
        $newProductProductAssociation->quantity = 1;

        return ($newProductProductAssociation);
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts($products)
    {
        $newProductProductAssociations = new ArrayCollection();
        foreach($products as $product) {
            $newProductProductAssociations->add($this->createProductAssociationFromProduct($product));
        }

        $this->productProductAssociations = $newProductProductAssociations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts()
    {
        return $this->productProductAssociations->map(function ($productProductAssociation) {
            return $productProductAssociation->product;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct(ProductInterface $product)
    {
        if (!$this->hasProduct($product)) {
            $this->productProductAssociations->add($this->createProductAssociationFromProduct($product));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProduct(ProductInterface $product)
    {
        return $this->productProductAssociations->exists(function ($key, $element) use ($product) {
            $element->product->getId() === $product->getId();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct(ProductInterface $product)
    {
        $this->productProductAssociations->filter(function ($productProductAssociation) use ($product) {
            return $productProductAssociation->product->getId() !== $product->getId();
        });

        return $this;
    }

    private function createProductModelAssociationFromProductModel(ProductModelInterface $productModel)
    {
        $newProductModelProductAssociation = $this->owner instanceof ProductInterface ? new ProductModelProductAssociation() : new ProductModelProductModelAssociation();
        $newProductModelProductAssociation->productModel = $productModel;
        $newProductModelProductAssociation->quantity = 1;

        return ($newProductModelProductAssociation);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductModels($productModels): void
    {
        $newProductModelProductAssociations = new ArrayCollection();
        foreach($productModels as $productModel) {
            $newProductModelProductAssociations->add($this->createProductModelAssociationFromProductModel($productModel));
        }

        $this->productModelProductAssociations = $newProductModelProductAssociations;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductModels()
    {
        return $this->productModelProductAssociations->map(function ($productModelProductAssociation) {
            return $productModelProductAssociation->productModel;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addProductModel(ProductModelInterface $productModel): void
    {
        if (!$this->hasProductModel($productModel)) {
            $this->productModelProductAssociations->add($this->createProductModelAssociationFromProductModel($productModel));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasProductModel(ProductModelInterface $productModel)
    {
        return $this->productModelProductAssociations->exists(function ($key, $element) use ($productModel) {
            $element->productModel->getId() === $productModel->getId();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeProductModel(ProductModelInterface $productModel): void
    {
        $this->productModelProductAssociations->filter(function ($productModelProductAssociation) use ($productModel) {
            return $productModelProductAssociation->productModel->getId() !== $productModel->getId();
        });
    }
























    private function createGroupAssociationFromGroup(GroupInterface $group)
    {
        $newGroupProductAssociation = $this->owner instanceof ProductInterface ? new GroupProductAssociation() : new GroupProductModelAssociation();
        $newGroupProductAssociation->group = $group;
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

        $this->groupProductAssociations = $newGroupProductAssociations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groupProductAssociations->map(function ($groupProductAssociation) {
            return $groupProductAssociation->group;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(GroupInterface $group)
    {
        if (!$this->hasGroup($group)) {
            $this->groupProductAssociations->add($this->createGroupAssociationFromGroup($group));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup(GroupInterface $group)
    {
        return $this->groupProductAssociations->exists(function ($key, $element) use ($group) {
            $element->group->getId() === $group->getId();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(GroupInterface $group)
    {
        $this->groupProductAssociations->filter(function ($groupProductAssociation) use ($group) {
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
