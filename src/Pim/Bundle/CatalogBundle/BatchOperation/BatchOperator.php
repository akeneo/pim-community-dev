<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\BatchOperation\BatchOperation;
use Pim\Bundle\CatalogBundle\BatchOperation\ChangeStatus;

/**
 * A batch operation operator
 * Contains a list of products and a batch operation to apply on them
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchOperator
{
    const OPERATION_CHANGE_STATUS = 'change_status';

    protected $productIds = array();

    protected $operation;

    protected $operationAlias;

    protected $manager;

    protected $operations = array();

    public function __construct(ProductManager $manager)
    {
        $this->manager = $manager;
    }

    public function getOperationChoices()
    {
        $choices = array();

        foreach (array_keys($this->operations) as $alias) {
            $choices[$alias] = sprintf('pim_catalog.batch_operation.%s.label', $alias);
        }

        return $choices;
    }

    public function registerBatchOperation($alias, BatchOperation $operation)
    {
        if (array_key_exists($alias, $this->operations)) {
            throw new \InvalidArgumentException(sprintf('Operation "%s" is already registered', $alias));
        }
        $this->operations[$alias] = $operation;
    }

    public function setProductIds($productIds)
    {
        $this->productIds = $productIds;

        return $this;
    }

    public function getProductIds()
    {
        return $this->productIds;
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

    /**
     * Delegate the batch operation execution to the chosen operation adapter
     */
    public function performOperation()
    {
        if ($this->operation) {
            $this->operation->perform($this->getProducts());
        }
    }

    /**
     * Get the product matching the stored product ids
     *
     * @return Product[]
     */
    private function getProducts()
    {
        return $this->manager->getFlexibleRepository()->findByIds($this->productIds);
    }
}
