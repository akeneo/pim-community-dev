<?php


declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class RemoveNonExistingProductValuesTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductQueryBuilderFactoryInterface */
    private $pqbFactory;

    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var BulkSaverInterface */
    private $productSaver;

    /** @var BulkSaverInterface */
    private $productModelSaver;

    /** @var int */
    private $batchSize;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        int $batchSize
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->channelRepository = $channelRepository;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     *
     * Loading the product filters the unexisting values. We just need to save it again.
     */
    public function execute()
    {
        $channel = $this->getConfiguredChannel();
        $filters = $this->getConfiguredFilters();

        $cursor = $this->getCursor($filters, $channel);
        $products = [];
        $productModels = [];
        while ($cursor->valid()) {
            $entity = $cursor->current();
            if ($entity instanceof ProductModelInterface) {
                $productModels[] = $entity;
            } elseif ($entity instanceof ProductInterface) {
                $products[] = $entity;
            }

            if (count($products) >= $this->batchSize) {
                $this->productSaver->saveAll($products);
                $products = [];
            }

            if (count($productModels) >= $this->batchSize) {
                $this->productModelSaver->saveAll($productModels);
                $productModels = [];
            }

            $cursor->next();
        }

        $this->productSaver->saveAll($products);
        $this->productModelSaver->saveAll($productModels);
    }

    /**
     * Returns the filters from the configuration.
     *
     * Here we transform the ID filter into SELF_AND_ANCESTOR.ID in order to retrieve
     * all the product models and products that are possibly impacted by the mass edit.
     */
    private function getConfiguredFilters(): array
    {
        $filters = $this->stepExecution->getJobParameters()->get('filters');

        return array_filter($filters, function ($filter) {
            return count($filter) > 0;
        });
    }

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
