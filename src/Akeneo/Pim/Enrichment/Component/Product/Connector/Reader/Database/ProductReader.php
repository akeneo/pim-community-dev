<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
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
class ProductReader implements ItemReaderInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var CursorInterface */
    protected $products;

    /** @var bool */
    private $firstRead = true;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param MetricConverter                     $metricConverter
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->channelRepository = $channelRepository;
        $this->metricConverter = $metricConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $channel = $this->getConfiguredChannel();
        $filters = $this->getConfiguredFilters();

        $this->products = $this->getProductsCursor($filters, $channel);
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

            $channel = $this->getConfiguredChannel();
            if (null !== $channel) {
                $this->metricConverter->convert($product, $channel);
            }
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

    /**
     * Returns the configured channel from the parameters.
     * If no channel is specified, returns null.
     *
     * @throws ObjectNotFoundException
     *
     * @return ChannelInterface|null
     */
    protected function getConfiguredChannel()
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

    /**
     * Get a filter by field name
     *
     * @param string $fieldName
     *
     * @return array
     */
    protected function getConfiguredFilter(string $fieldName)
    {
        $filters = $this->getConfiguredFilters();

        return array_values(array_filter($filters, function ($filter) use ($fieldName) {
            return $filter['field'] === $fieldName;
        }))[0] ?? null;
    }

    /**
     * @param array            $filters
     * @param ChannelInterface $channel
     *
     * @return CursorInterface
     */
    protected function getProductsCursor(array $filters, ChannelInterface $channel = null)
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
}
