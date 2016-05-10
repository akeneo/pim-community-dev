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

    /** @var ProductExportBuilderOptionsResolver */
    protected $optionsResolver;

    /** @var ProductExportBuilderFiltersResolver */
    protected $filtersResolver;

    /** @var CursorInterface */
    protected $products;

    /**
     * TODO: remove this (and the getter/setter) with nidup's PR
     *
     * @var string
     */
    protected $channelCode;

    /**
     * TODO: remove this (and the getter/setter) with nidup's PR
     *
     * @var ChannelInterface
     */
    protected $channel;

    /**
     * TODO: remove this with nidup's PR
     *
     * @var array
     */
    protected $uiOptions;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelRepositoryInterface          $channelRepository
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param ObjectDetacherInterface             $objectDetacher
     * @param ProductExportBuilderOptionsResolver $optionsResolver
     * @param ProductExportBuilderFiltersResolver $filtersResolver,
     * @param bool                                $generateCompleteness
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ObjectDetacherInterface $objectDetacher,
        ProductExportBuilderOptionsResolver $optionsResolver,
        ProductExportBuilderFiltersResolver $filtersResolver,
        $generateCompleteness
    ) {
        $this->pqbFactory           = $pqbFactory;
        $this->channelRepository    = $channelRepository;
        $this->completenessManager  = $completenessManager;
        $this->metricConverter      = $metricConverter;
        $this->objectDetacher       = $objectDetacher;
        $this->optionsResolver      = $optionsResolver;
        $this->filtersResolver      = $filtersResolver;
        $this->generateCompleteness = (bool) $generateCompleteness;
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
        return $this->uiOptions['enabled'];
    }

    /**
     * @param string $enabled
     */
    public function setEnabled($enabled)
    {
        $this->uiOptions['enabled'] = $enabled;
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

        $uiOptions = $this->optionsResolver->resolve($this->uiOptions);
        $filters = array_merge(
            $this->getDefaultFilters($this->channel),
            $this->filtersResolver->resolve($uiOptions)
        );

        $pqb = $this->pqbFactory->create(['default_scope' => $this->channel->getCode()]);

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
     * TODO: will be progressively removed with the coming stories about the Product Export Builder
     *
     * Return the filters to be applied on the PQB instance.
     *
     * @param ChannelInterface $channel
     *
     * @return array
     */
    protected function getDefaultFilters(ChannelInterface $channel)
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
