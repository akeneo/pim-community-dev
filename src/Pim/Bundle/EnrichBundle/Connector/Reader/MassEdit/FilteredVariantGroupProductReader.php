<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ORM\EntityNotFoundException;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\VariantGroupCleaner;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product reader for mass edit, skipping products not usable in variant group
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredVariantGroupProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var FilteredProductReader */
    protected $productReader;

    /** @var VariantGroupCleaner */
    protected $cleaner;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var bool */
    protected $isExecuted;

    /** @var string */
    protected $channel;

    /**
     * @param FilteredProductReader $reader
     * @param VariantGroupCleaner   $cleaner
     */
    public function __construct(FilteredProductReader $reader, VariantGroupCleaner $cleaner)
    {
        $this->productReader = $reader;
        $this->cleaner = $cleaner;
        $this->isExecuted = false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->isExecuted) {
            $this->isExecuted = true;
            $configuration = $this->cleaner->clean($this->getConfiguration(), $this->stepExecution);
            if (null === $configuration) {
                return null;
            }
            $this->productReader->setConfiguration($configuration);
            $this->productReader->setStepExecution($this->stepExecution);
        }

        return $this->productReader->read();
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
     */
    public function initialize()
    {
        $this->isExecuted = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return ['filters' => [], 'actions' => []];
    }

    /**
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
