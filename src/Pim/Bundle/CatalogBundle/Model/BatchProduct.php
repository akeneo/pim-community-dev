<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\BatchOperation\EditAttribute;

/**
 * A batch of products with the operation to apply on them
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchProduct
{
    const OPERATION_EDIT_ATTRIBUTES = 'edit_attributes';

    protected $products;

    protected $operation;

    protected $operations = array();

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->operations = array(
            self::OPERATION_EDIT_ATTRIBUTES => new EditAttribute()
        );
    }

    public static function getOperationChoices()
    {
        return array(
            self::OPERATION_EDIT_ATTRIBUTES => 'Edit attributes',
        );
    }

    public function setProducts(Collection $products)
    {
        $this->products = $products;

        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    public function getOperation()
    {
        return $this->operation;
    }
}
