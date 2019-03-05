<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\MetricConverter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Product reader that only returns product entities and skips product models.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredProductAndProductModelReader implements
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

    /** @var bool */
    private $readChildren;

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
     * @param bool                                $readChildren
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        MetricConverter $metricConverter,
        bool $readChildren
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->channelRepository = $channelRepository;
        $this->metricConverter = $metricConverter;
        $this->readChildren = $readChildren;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->firstRead = true;

        $channel = $this->getConfiguredChannel();

        $filters = $this->getConfiguredFilters();
        $this->productsAndProductModels = $this->getProductsCursor($filters, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): ?ProductInterface
    {
        $product = null;
        $product = $this->getNextProduct();

        if (null !== $product) {
            $channel = $this->getConfiguredChannel();
            if (null !== $channel) {
                $this->metricConverter->convert($product, $channel);
            }
        }

        return $product;
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

        if ($this->readChildren) {
            $filters = array_map(function ($filter) {
                if ('id' === $filter['field']) {
                    $filter['field'] = 'self_and_ancestor.id';
                }

                if ('label_or_identifier' === $filter['field']) {
                    $filter['field'] = 'self_and_ancestor.label_or_identifier';
                }

                return $filter;
            }, $filters);
        }

        return array_filter($filters, function ($filter) {
            return count($filter) > 0;
        });
    }

    /**
     * @param array            $filters
     * @param ChannelInterface $channel
     *
     * @return CursorInterface
     */
    private function getProductsCursor(array $filters, ChannelInterface $channel = null): CursorInterface
    {
        $options = ['filters' => $filters];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }

        $productQueryBuilder = $this->pqbFactory->create($options);

        return $productQueryBuilder->execute();
    }

    /**
     * This reader makes sure we return only product entities.
     *
     * @return null|ProductInterface
     */
    private function getNextProduct(): ?ProductInterface
    {
        $entity = null;

        while ($this->productsAndProductModels->valid()) {
            if (!$this->firstRead) {
                $this->productsAndProductModels->next();
            }

            $this->firstRead = false;
            $entity = $this->productsAndProductModels->current();
            if (false === $entity) {
                return null;
            }

            if ($entity instanceof ProductModelInterface) {
                if ($this->stepExecution) {
                    if (!$this->readChildren) {
                        $warning = 'This bulk action doesn\'t support Product models entities yet.';
                        $this->stepExecution->addWarning(
                            $warning,
                            [],
                            new DataInvalidItem(['code' => $entity->getCode()])
                        );
                    }
                }

                $entity = null;
                continue;
            }

            $this->stepExecution->incrementSummaryInfo('read');

            break;
        }

        return $entity;
    }
}
