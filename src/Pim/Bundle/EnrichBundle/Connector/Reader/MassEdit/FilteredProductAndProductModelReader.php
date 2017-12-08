<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Product reader that only returns product entities and skips product models.
 *
 * TODO: This class is unused for now (replaced by both FilteredProductReader and FilteredProductModelReader).
 * This class has to be used for mass actions PIM-6357 after removing the skip part.
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

    /** @var CompletenessManager */
    private $completenessManager;

    /** @var MetricConverter */
    private $metricConverter;

    /** @var bool */
    private $generateCompleteness;

    /** @var StepExecution */
    private $stepExecution;

    /** @var CursorInterface */
    private $productsAndProductModels;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param bool                                $generateCompleteness
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        $generateCompleteness
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->channelRepository = $channelRepository;
        $this->completenessManager = $completenessManager;
        $this->metricConverter = $metricConverter;
        $this->generateCompleteness = (bool) $generateCompleteness;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $channel = $this->getConfiguredChannel();
        if (null !== $channel && $this->generateCompleteness) {
            $this->completenessManager->generateMissingForChannel($channel);
        }

        $filters = $this->getConfiguredFilters();
        $this->productsAndProductModels = $this->getProductsCursor($filters, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): ?EntityWithFamilyInterface
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
    private function getNextProduct(): ?EntityWithFamilyInterface
    {
        $entity = null;

        while ($this->productsAndProductModels->valid()) {
            $entity = $this->productsAndProductModels->current();

            $this->productsAndProductModels->next();

            // if ($entity instanceof ProductModelInterface) {
            //     if ($this->stepExecution) {
            //         $this->stepExecution->incrementSummaryInfo('skip');

            //         $warning = 'Bulk actions do not support Product models entities yet.';
            //         $this->stepExecution->addWarning(
            //             $warning,
            //             [],
            //             new DataInvalidItem(['code' => $entity->getCode()])
            //         );
            //     }

            //     $entity = null;
            //     continue;
            // }

            $this->stepExecution->incrementSummaryInfo('read');

            break;
        }

        return $entity;
    }
}
