<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Item\InitializableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Special reader that will select all the ancestry of the selected items
 * (to get all the product models and products that are possibly impacted by the mass edit).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelReader implements
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

    /** @var StepExecution */
    private $stepExecution;

    /** @var CursorInterface */
    private $productsAndProductModels;

    /** @var bool  */
    private $firstRead = true;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param CompletenessManager                 $completenessManager
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->channelRepository = $channelRepository;
        $this->completenessManager = $completenessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $this->firstRead = true;

        $channel = $this->getConfiguredChannel();
        if (null !== $channel) {
            $this->completenessManager->generateMissingForChannel($channel);
        }

        $filters = $this->getConfiguredFilters();
        $this->productsAndProductModels = $this->getCursor($filters, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): ?EntityWithFamilyInterface
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
     * Here we transform the ID filter into SELF_AND_ANCESTOR.ID in order to retrieve
     * all the product models and products that are possibly impacted by the mass edit.
     *
     * @return array
     */
    private function getConfiguredFilters(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        if (array_key_exists('data', $filters)) {
            $filters = $filters['data'];
        }

        $filters = array_map(function ($filter) {
            if ('id' === $filter['field']) {
                $filter['field'] = 'self_and_ancestor.id';
            }

            return $filter;
        }, $filters);

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
    private function getCursor(array $filters, ChannelInterface $channel = null): CursorInterface
    {
        $options = ['filters' => $filters];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }

        $queryBuilder = $this->pqbFactory->create($options);

        return $queryBuilder->execute();
    }
}
