<?php

namespace Akeneo\ActivityManager\Component\Reader\Database;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var CursorInterface */
    protected $products;

    /** @var ItemReaderInterface */
    private $productReader;

    /**
     * @param InitializableInterface $productReader
     */
    public function __construct(InitializableInterface $productReader)
    {
        $this->productReader = $productReader;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->productReader->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;

        if ($this->products->valid()) {
            $product = $this->products->current();
            $this->stepExecution->incrementSummaryInfo('read');
            $this->products->next();
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
