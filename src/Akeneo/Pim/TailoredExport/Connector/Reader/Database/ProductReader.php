<?php

namespace Akeneo\Pim\TailoredExport\Connector\Reader\Database;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

class ProductReader implements
    ItemReaderInterface,
    InitializableInterface,
    StepExecutionAwareInterface,
    TrackableItemReaderInterface
{
    private ProductQueryBuilderFactoryInterface $pqbFactory;
    private ?StepExecution $stepExecution;
    private ?CursorInterface $products;
    private bool $firstRead = true;

    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory)
    {
        $this->pqbFactory = $pqbFactory;
    }

    public function totalItems(): int
    {
        if (null === $this->products) {
            throw new \RuntimeException('Unable to compute the total items the reader will process until the reader is not initialized');
        }

        return $this->products->count();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $filters = $this->getConfiguredFilters();

        $this->products = $this->getProductsCursor($filters);
        $this->firstRead = true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;

        if ($this->products->valid()) {
            if (!$this->firstRead) {
                $this->products->next();
            }
            $product = $this->products->current();
        }

        if (null !== $product) {
            $this->stepExecution->incrementSummaryInfo('read');
        }

        $this->firstRead = false;

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    private function getConfiguredFilters(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        if (array_key_exists('data', $filters)) {
            $filters = $filters['data'];
        }

        return array_filter($filters, function ($filter) {
            return count($filter) > 0;
        });
    }

    private function getProductsCursor(array $filters): CursorInterface
    {
        $productQueryBuilder = $this->pqbFactory->create();
        foreach ($filters as $filter) {
            $productQueryBuilder->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context'] ?? []
            );
        }

        return $productQueryBuilder->execute();
    }
}
