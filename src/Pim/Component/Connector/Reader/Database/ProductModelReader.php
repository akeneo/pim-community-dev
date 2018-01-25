<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Reader\Database;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

/**
 * The product model reader using the Product Model Query Builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var CursorInterface */
    protected $productModels;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     */
    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory)
    {
        $this->pqbFactory = $pqbFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $filters = $this->getConfiguredFilters();
        $this->productModels = $this->getProductModelsCursor($filters);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $productModel = null;

        if ($this->productModels->valid()) {
            $productModel = $this->productModels->current();
            $this->stepExecution->incrementSummaryInfo('read');
            $this->productModels->next();
        }

        return $productModel;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function getProductModelsCursor(array $filters)
    {
        $options = ['filters' => $filters];

        $productQueryBuilder = $this->pqbFactory->create($options);

        return $productQueryBuilder->execute();
    }

    /**
     * Returns the filters from the configuration.
     * The parameters can be in the 'filters' root node, or in filters data node (e.g. for export).
     *
     * @return array
     */
    protected function getConfiguredFilters()
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        if (array_key_exists('data', $filters)) {
            $filters = $filters['data'];
        }

        return array_filter($filters, function ($filter) {
            return count($filter) > 0;
        });
    }
}
