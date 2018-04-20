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

    /** @var bool */
    private $firstRead = true;

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
        $this->productModels = $this->getProductModelsCursor();
        $this->firstRead = true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $productModel = null;

        if ($this->productModels->valid()) {
            if (!$this->firstRead) {
                $this->productModels->next();
            }
            $productModel = $this->productModels->current();
            $this->stepExecution->incrementSummaryInfo('read');
        }

        $this->firstRead = false;

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
     * @return CursorInterface
     */
    private function getProductModelsCursor()
    {
        $productQueryBuilder = $this->pqbFactory->create([]);

        return $productQueryBuilder->execute();
    }
}
