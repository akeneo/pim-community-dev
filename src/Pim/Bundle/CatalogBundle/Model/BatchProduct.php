<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\BatchOperation\EditAttribute;
use Pim\Bundle\CatalogBundle\BatchOperation\ChangeStatus;

/**
 * A batch of products with the operation to apply on them
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchProduct
{
    const OPERATION_CHANGE_STATUS = 'change_status';

    protected $products;

    protected $operation;

    protected $operationAlias;

    protected $operations = array();

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->operations = array(
            self::OPERATION_CHANGE_STATUS => new ChangeStatus()
        );
    }

    public static function getOperationChoices()
    {
        return array(
            self::OPERATION_CHANGE_STATUS => 'Change status (Enabled/Disabled)',
        );
    }

    public function setProducts($products)
    {
        if (!$products instanceof Collection) {
            $products = new ArrayCollection($products);
        }
        $this->products = $products;

        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function getProductIds()
    {
        return $this->products->map(
            function ($product) {
                return $product->getId();
            }
        )->toArray();
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

    public function setOperationAlias($operationAlias)
    {
        $this->operationAlias = $operationAlias;

        if (!isset($this->operations[$operationAlias])) {
            throw new \Exception;
        }
        $this->operation = $this->operations[$operationAlias];

        return $this;
    }

    public function getOperationAlias()
    {
        return $this->operationAlias;
    }

    public function performOperation()
    {
        if ($this->operation) {
            return $this->operation->perform($this->products);
        }
    }
}
