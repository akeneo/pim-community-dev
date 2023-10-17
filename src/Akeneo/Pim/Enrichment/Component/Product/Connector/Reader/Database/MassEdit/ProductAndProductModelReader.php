<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\MassEdit;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Webmozart\Assert\Assert;

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
    StepExecutionAwareInterface,
    TrackableItemReaderInterface
{
    private ?StepExecution $stepExecution = null;
    private ?CursorInterface $productsAndProductModels = null;

    public function __construct(
        private readonly ProductQueryBuilderFactoryInterface $pqbFactory,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductModelRepositoryInterface $productModelRepository,
        private readonly bool $readChildren
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $channel = $this->getConfiguredChannel();

        $filters = $this->getConfiguredFilters();
        $this->productsAndProductModels = $this->getCursor($filters, $channel);
        $this->productsAndProductModels->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function read(): ?EntityWithFamilyInterface
    {
        $entity = null;
        while (null === $entity && $this->productsAndProductModels->valid()) {
            $identifier = $this->productsAndProductModels->current();
            Assert::isInstanceOf($identifier, IdentifierResult::class);
            $entity = match ($identifier->getType()) {
                ProductInterface::class => $this->productRepository->findOneBy(
                    ['uuid' => \preg_replace('/^product_/', '', $identifier->getId())]
                ),
                ProductModelInterface::class => $this->productModelRepository->findOneByIdentifier($identifier->getIdentifier()),
            };
            $this->productsAndProductModels->next();
        }
        if (null !== $entity) {
            $this->stepExecution->incrementSummaryInfo('read');
        }

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
    private function getCursor(array $filters, ChannelInterface $channel = null): CursorInterface
    {
        $options = ['filters' => $filters];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }

        $queryBuilder = $this->pqbFactory->create($options);

        return $queryBuilder->execute();
    }

    public function totalItems(): int
    {
        if (null === $this->productsAndProductModels) {
            throw new \RuntimeException('Unable to compute the total items the reader will process until the reader is not initialized');
        }

        return $this->productsAndProductModels->count();
    }
}
