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
class AbstractProductModelProductAssociation
{
  public $quantity;

  /** @var ProductModelInterface */
  public $productModel;

  public $association;

  public function getProductModel()
  {
      return $this->productModel;
  }

  public function getQuantity()
  {
      return $this->quantity;
  }

  public function __toString()
  {
      return $this->productModel->getCode() . '_' . $this->getQuantity();
  }
}
