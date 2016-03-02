<?php

namespace Pim\Bundle\EnrichBundle\Connector\Step;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\TemporaryFileCleaner;
use Pim\Bundle\EnrichBundle\Step\MassEditRemoveTemporaryMediaStep;

/**
 * BatchBundle Step for standard mass edit products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditStep extends AbstractStep
{
    /** @var array */
    protected $configuration;

    /** @var StepExecutionAwareInterface */
    protected $cleaner;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->cleaner->setStepExecution($stepExecution);
        $this->cleaner->execute($this->configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        $this->configuration = $config;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableStepElements()
    {
        return [];
    }

    /**
     * @return TemporaryFileCleaner
     */
    public function getCleaner()
    {
        return $this->cleaner;
    }

    /**
     * @param StepExecutionAwareInterface $cleaner
     *
     * @return MassEditStep
     */
    public function setCleaner(StepExecutionAwareInterface $cleaner)
    {
        $this->cleaner = $cleaner;

        return $this;
    }
}
