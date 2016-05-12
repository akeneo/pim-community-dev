<?php

namespace Pim\Component\Connector\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
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

    /** @var bool */
    protected $generateCompleteness;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var CursorInterface */
    protected $products;

    /** @var string */
    protected $channelCode;

    /** @var ChannelInterface */
    protected $channel;

    /** @var string */
    protected $enabled;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param ObjectDetacherInterface             $objectDetacher
     * @param bool                                $generateCompleteness
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ObjectDetacherInterface $objectDetacher,
        $generateCompleteness
    ) {
        $this->pqbFactory           = $pqbFactory;
        $this->channelRepository    = $channelRepository;
        $this->completenessManager  = $completenessManager;
        $this->metricConverter      = $metricConverter;
        $this->objectDetacher       = $objectDetacher;
        $this->generateCompleteness = (bool) $generateCompleteness;
        $this->enabled              = 'enabled'; // enabled by default to be compatible with former/custom exports
    }

    /**
     * @param string $channelCode
     */
    public function setChannel($channelCode)
    {
        $this->channelCode = $channelCode;
        $this->channel     = null;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channelCode;
    }

    /**
     * @return string
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelRepository->getLabelsIndexedByCode(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.channel.label',
                    'help'     => 'pim_connector.export.channel.help',
                    'attr'     => ['data-tab' => 'content']
                ],
            ],
            'enabled' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => [
                        'enabled'  => 'pim_connector.export.status.choice.enabled',
                        'disabled' => 'pim_connector.export.status.choice.disabled',
                        'all'      => 'pim_connector.export.status.choice.all'
                    ],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_connector.export.status.label',
                    'help'     => 'pim_connector.export.status.help',
                    'attr'     => ['data-tab' => 'content']
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->channel = $this->getChannelByCode($this->channelCode);

        $pqb     = $this->pqbFactory->create(['default_scope' => $this->channel->getCode()]);
        $filters = $this->getFilters($this->channel, $this->rawToStandardProductStatus($this->enabled));

        foreach ($filters as $filter) {
            $pqb->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context']
            );
        }

        if ($this->generateCompleteness) {
            $this->completenessManager->generateMissingForChannel($this->channel);
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
            $this->metricConverter->convert($product, $this->channel);
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
     * @param string $code
     *
     * @throws ObjectNotFoundException
     * @return ChannelInterface
     */
    protected function getChannelByCode($code)
    {
        $channel = $this->channelRepository->findOneByIdentifier($code);
        if (null === $channel) {
            throw new ObjectNotFoundException(sprintf('Channel with "%s" code does not exist', $code));
        }

        return $channel;
    }

    /**
     * Return the filters to be applied on the PQB instance.
     *
     * @param ChannelInterface $channel
     * @param bool             $status
     *
     * @return array
     */
    protected function getFilters(ChannelInterface $channel, $status)
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

        if (null !== $status) {
            $filters[] = [
                'field'    => 'enabled',
                'operator' => Operators::EQUALS,
                'value'    => $status,
                'context'  => []
            ];
        }

        return $filters;
    }

    /**
     * Convert the UI product status to the standard product status
     *
     * @param string $rawStatus
     *
     * @return bool|null
     */
    protected function rawToStandardProductStatus($rawStatus)
    {
        switch ($rawStatus) {
            case 'enabled':
                $status = true;
                break;
            case 'disabled':
                $status = false;
                break;
            default:
                $status = null;
        }

        return $status;
    }
}
