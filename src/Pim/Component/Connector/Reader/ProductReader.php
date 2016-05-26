<?php

namespace Pim\Component\Connector\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;

/**
 * Storage-agnostic product reader using the Product Query Builder
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReader extends AbstractConfigurableStepElement implements ItemReaderInterface, StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var bool */
    protected $generateCompleteness;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var CursorInterface */
    protected $products;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param ObjectDetacherInterface             $objectDetacher
     * @param JobRepositoryInterface              $jobRepository
     * @param bool                                $generateCompleteness
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ObjectDetacherInterface $objectDetacher,
        JobRepositoryInterface $jobRepository,
        $generateCompleteness
    ) {
        $this->pqbFactory           = $pqbFactory;
        $this->channelRepository    = $channelRepository;
        $this->completenessManager  = $completenessManager;
        $this->metricConverter      = $metricConverter;
        $this->objectDetacher       = $objectDetacher;
        $this->jobRepository        = $jobRepository;
        $this->generateCompleteness = (bool) $generateCompleteness;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $channel = $this->getConfiguredChannel();
        $parameters = $this->stepExecution->getJobParameters();
        $filters = $parameters->get('filters');
        $pqb = $this->pqbFactory->create(['default_scope' => $channel->getCode()]);

        foreach ($filters as $field => $filter) {
            $pqb->addFilter(
                $field,
                $filter['operator'],
                $filter['value'],
                $filter['context']
            );
        }

        if ($this->generateCompleteness) {
            $this->completenessManager->generateMissingForChannel($channel);
        }

        $this->products = $pqb->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;

        if ($this->products->valid()) {
            $product = $this->products->current();
            $this->stepExecution->incrementSummaryInfo('read');
            $this->products->next();
        }

        if (null !== $product) {
            $this->objectDetacher->detach($product);
            $channel = $this->getConfiguredChannel();
            $this->metricConverter->convert($product, $channel);
        }

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
     * @throws ObjectNotFoundException
     *
     * @return ChannelInterface
     */
    protected function getConfiguredChannel()
    {
        $parameters = $this->stepExecution->getJobParameters();
        $channelCode = $parameters->get('channel');
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw new ObjectNotFoundException(sprintf('Channel with "%s" code does not exist', $channelCode));
        }

        return $channel;
    }

    /**
     * Return the filters to be applied on the PQB instance.
     *
     * @param ChannelInterface $channel
     * @param string           $updated
     *
     * @return array
     */
    protected function getFilters(ChannelInterface $channel, $updated)
    {
        $filters = [
            [
                'field'    => 'completeness',
                'operator' => Operators::EQUALS,
                'value'    => 100,
                'context'  => []
            ],
            [
                'field'    => 'categories.id',
                'operator' => Operators::IN_CHILDREN_LIST,
                'value'    => [$channel->getCategory()->getId()],
                'context'  => []
            ]
        ];

        if (null !== $updated) {
            $filters[] = [
                'field'    => 'updated',
                'operator' => Operators::GREATER_THAN,
                'value'    => $updated,
                'context'  => []
            ];
        }

        return $filters;
    }

    /**
     * Convert the UI product updated to the standard product updated
     *
     * @param string $rawUpdated
     *
     * @return \DateTime|null
     */
    protected function rawToStandardProductUpdated($rawUpdated)
    {
        switch ($rawUpdated) {
            case 'last_export':
                $jobInstance = $this->stepExecution->getJobExecution()->getJobInstance();
                $jobExecution = $this->jobRepository->getLastJobExecution($jobInstance, BatchStatus::COMPLETED);
                $updated = null === $jobExecution ? null : $jobExecution->getStartTime();
                break;
            default:
                $updated = null;
        }

        return $updated;
    }
}
