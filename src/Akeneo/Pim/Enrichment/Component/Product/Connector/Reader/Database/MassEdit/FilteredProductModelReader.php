<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Product reader that only returns product model entities and skips simple products.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredProductModelReader implements
    ItemReaderInterface,
    InitializableInterface,
    StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var MetricConverter */
    private $metricConverter;

    /** @var StepExecution */
    private $stepExecution;

    /** @var CursorInterface */
    private $productsAndProductModels;

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
    public function initialize(): void
    {
        $this->firstRead = true;

        $channel = $this->getConfiguredChannel();
        $filters = $this->getConfiguredFilters();
        $this->productsAndProductModels = $this->getProductModelsCursor($filters, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): ?ProductModelInterface
    {
        $productModel = null;
        $productModel = $this->getNextProductModel();

        if (null !== $productModel) {
            $channel = $this->getConfiguredChannel();
            if (null !== $channel) {
                $this->metricConverter->convert($productModel, $channel);
            }
        }

        return $productModel;
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
     *
     * @throws ObjectNotFoundException
     *
     * @return ChannelInterface|null
     */
    private function getConfiguredChannel(): ?ChannelInterface
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

    /**
     * @param array                 $filters
     * @param ChannelInterface|null $channel
     *
     * @return CursorInterface
     */
    private function getProductModelsCursor(array $filters, ChannelInterface $channel = null): CursorInterface
    {
        $filters[] = [
            'field' => 'entity_type',
            'operator' => '=',
            'value' => ProductModelInterface::class,
        ];
        $options = ['filters' => $filters];
        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }

        $productModelQueryBuilder = $this->pqbFactory->create($options);

        return $productModelQueryBuilder->execute();
    }

    /**
     * This reader makes sure we return only product model entities.
     *
     * @return null|ProductModelInterface
     */
    private function getNextProductModel(): ?ProductModelInterface
    {
        $entity = null;

        if ($this->productsAndProductModels->valid()) {
            if (!$this->firstRead) {
                $this->productsAndProductModels->next();
            }

            $entity = $this->productsAndProductModels->current();
            if (false === $entity) {
                return null;
            }
            $this->stepExecution->incrementSummaryInfo('read');
        }
        $this->firstRead = false;

        return $entity;
    }
}
