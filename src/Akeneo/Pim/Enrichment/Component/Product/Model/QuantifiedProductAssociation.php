<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Product association entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedProductAssociation implements QuantifiedAssociationInterface
{
  /** @var int|string */
  protected $id;

  /** @var AssociationTypeInterface */
  protected $associationType;

  /** @var EntityWithAssociationsInterface */
  protected $owner;

  protected $quantifiedProductProductAssociation;

  /**
   * Constructor
   */
  public function __construct()
  {
      $this->quantifiedProductProductAssociation = new ArrayCollection();
  }

  public function getQuantifiedProducts()
  {
      return $this->quantifiedProductProductAssociation;
  }

  /**
   * {@inheritdoc}
   */
  public function addQuantifiedProduct(QuantifiedProductProductAssociation $quantifiedProduct)
  {
      if (!$this->quantifiedProductProductAssociation->contains($quantifiedProduct)) {
          $this->quantifiedProductProductAssociation->add($quantifiedProduct);
      }

      return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasQuantifiedProduct(QuantifiedProductProductAssociation $quantifiedProduct)
  {
      return $this->quantifiedProductProductAssociation->contains($quantifiedProduct);
  }

  /**
   * {@inheritdoc}
   */
  public function removeQuantifiedProduct(QuantifiedProductProductAssociation $quantifiedProduct)
  {
      $this->quantifiedProductProductAssociation->removeElement($quantifiedProduct);

      return $this;
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
      // if (!$this->owner) {
      //     $this->owner = $owner;
      //     $owner->addAssociation($this);
      // }

      return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner()
  {
      return $this->owner;
  }

  /**
   * {@inheritdoc}
   */
  public function getReference()
  {
      return 'no_idea';
      // return $this->owner ? $this->owner->getIdentifier() . '.' . $this->associationType->getCode() : null;
  }

    public function removeProductForIdentifier(string $productIdentifierToDelete): void
    {
        $quantifiedProductProductAssociationToRemove = $this->getQuantifiedProductProductAssociation($productIdentifierToDelete);
        $this->quantifiedProductProductAssociation->removeElement($quantifiedProductProductAssociationToRemove);
    }

    public function updateProductForIdentifier($productTomodify, $quantity): void
    {
        $quantifiedProductProductAssociationToRemove = $this->getQuantifiedProductProductAssociation($productTomodify);
        $quantifiedProductProductAssociationToRemove->quantity = $quantity;
    }

    public function addProductForIdentifier(QuantifiedProductProductAssociation $productProductAssociationToAdd): void
    {
        $this->quantifiedProductProductAssociation->add($productProductAssociationToAdd);
    }

    private function getQuantifiedProductProductAssociation(string $productIdentifierToDelete)
    {
        $quantifiedProductProductAssociationToRemove = $this->quantifiedProductProductAssociation
            ->filter(
                function (QuantifiedProductProductAssociation $quantifiedProductProductAssociation) use (
                    $productIdentifierToDelete
                ) {
                    return $quantifiedProductProductAssociation->product->getReference() === $productIdentifierToDelete;
                }
            )
            ->first();

        return $quantifiedProductProductAssociationToRemove;
}
}
