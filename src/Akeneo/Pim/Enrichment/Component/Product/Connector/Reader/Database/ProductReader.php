<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Storage-agnostic product reader using the Product Query Builder
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface, TrackableItemReaderInterface, StatefulInterface
{
    protected ?StepExecution $stepExecution = null;
    protected ?CursorInterface $products = null;
    protected bool $firstRead = true;

    public function __construct(
        protected ProductQueryBuilderFactoryInterface $pqbFactory,
        protected ChannelRepositoryInterface $channelRepository,
        protected MetricConverter $metricConverter
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $state = []): void
    {
        $channel = $this->getConfiguredChannel();
        $filters = $this->getConfiguredFilters();

        $this->products = $this->getProductsCursor($filters, $channel);
        $this->products->rewind();
        $this->firstRead = true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->firstRead) {
            $this->products->next();
        }
        $this->firstRead = false;
        if ($this->products->valid()) {
            $product = $this->products->current();
            $this->stepExecution->incrementSummaryInfo('read');

            $channel = $this->getConfiguredChannel();
            if (null !== $channel) {
                $this->metricConverter->convert($product, $channel);
            }

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
     * Returns the configured channel from the parameters.
     * If no channel is specified, returns null.
     *
     * @throws ObjectNotFoundException
     */
    protected function getConfiguredChannel(): ?ChannelInterface
    {
        $parameters = $this->stepExecution->getJobParameters();
        if (!isset($parameters->get('filters')['structure']['scope'])) {
            return null;
        }

        $channelCode = $parameters->get('filters')['structure']['scope'];
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw new ObjectNotFoundException(sprintf('Channel with "%s" code does not exist', $channelCode));
        }

        return $channel;
    }

    /**
     * Returns the filters from the configuration.
     * The parameters can be in the 'filters' root node, or in filters data node (e.g. for export).
     */
    protected function getConfiguredFilters(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        if (array_key_exists('data', $filters)) {
            $filters = $filters['data'];
        }

        return array_filter($filters, function ($filter) {
            return count($filter) > 0;
        });
    }

    /**
     * Get a filter by field name
     */
    protected function getConfiguredFilter(string $fieldName): ?array
    {
        $filters = $this->getConfiguredFilters();

        return array_values(array_filter($filters, function ($filter) use ($fieldName) {
            return $filter['field'] === $fieldName;
        }))[0] ?? null;
    }

    protected function getProductsCursor(array $filters, ChannelInterface $channel = null): CursorInterface
    {
        $options = null !== $channel ? ['default_scope' => $channel->getCode()] : [];

        $productQueryBuilder = $this->pqbFactory->create($options);
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

    public function totalItems(): int
    {
        if (null === $this->products) {
            throw new \RuntimeException('Unable to compute the total items the reader will process until the reader is not initialized');
        }

        return $this->products->count();
    }

    public function getState(): array
    {
        return [
            'last_position_read' => $this->products?->key(),
        ];
    }
}
