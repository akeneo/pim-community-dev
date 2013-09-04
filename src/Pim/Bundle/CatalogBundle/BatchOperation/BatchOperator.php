<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\BatchOperation\BatchOperation;

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
    /**
     * @var array $productIds
     */
    protected $productIds = array();

    /**
     * @var BatchOperation $operation
     */
    protected $operation;

    /**
     * @var string $operationAlias
     */
    protected $operationAlias;

    /**
     * @var FlexibleManager $manager
     */
    protected $manager;

    /**
     * @var BatchOperation[] $operations
     */
    protected $operations = array();

    /**
     * @param FlexibleManager $manager
     */
    public function __construct(FlexibleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Register a batch operation into the operator
     *
     * @param string         $alias
     * @param BatchOperation $operation
     *
     * @throw \InvalidArgumentException
     */
    public function registerBatchOperation($alias, BatchOperation $operation)
    {
        if (array_key_exists($alias, $this->operations)) {
            throw new \InvalidArgumentException(sprintf('Operation "%s" is already registered', $alias));
        }
        $this->operations[$alias] = $operation;
    }

    public function getOperationChoices()
    {
        $choices = array();

        foreach (array_keys($this->operations) as $alias) {
            $choices[$alias] = sprintf('pim_catalog.batch_operation.%s.label', $alias);
        }

        return $choices;
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
            throw new \InvalidArgumentException(sprintf('Operation "%s" is not registered', $operationAlias));
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
