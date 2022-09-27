<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

class ProductReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface, TrackableItemReaderInterface
{
    private ?StepExecution $stepExecution = null;
    /** @var CursorInterface<ProductInterface>|null */
    private ?CursorInterface $products = null;

    public function __construct(
        private ProductQueryBuilderFactoryInterface $pqbFactory,
    ) {
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
    public function initialize(): void
    {
        $filters = $this->getConfiguredFilters();

        $this->products = $this->getProductsCursor($filters);
        $this->products->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (null === $this->products) {
            throw new \Exception('Reader have not been properly initialized');
        }

        if ($this->products->valid()) {
            $product = $this->products->current();

            $this->getStepExecution()->incrementSummaryInfo('read');

            $this->products->next();

            return $product;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return array<array>
     */
    private function getConfiguredFilters(): array
    {
        $filters = [];
        if ($this->getStepExecution()->getJobParameters()->has('filters')) {
            $filters = $this->getStepExecution()->getJobParameters()->get('filters')['data'] ?? [];
        }

        return array_filter($filters, static fn ($filter) => (is_countable($filter) ? count($filter) : 0) > 0);
    }

    /**
     * @param array<array> $filters
     *
     * @return CursorInterface<ProductInterface>
     */
    private function getProductsCursor(array $filters): CursorInterface
    {
        $productQueryBuilder = $this->pqbFactory->create();
        foreach ($filters as $filter) {
            $productQueryBuilder->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context'] ?? [],
            );
        }

        return $productQueryBuilder->execute();
    }

    private function getStepExecution(): StepExecution
    {
        if (!$this->stepExecution instanceof StepExecution) {
            throw new \Exception('Reader have not been properly initialized');
        }

        return $this->stepExecution;
    }
}
