<?php

namespace Pim\Bundle\EnrichBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Cleaner\MassEditTemporaryFileCleaner;

/**
 * BatchBundle Step for standard mass edit products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditRemoveTemporaryMediaStep extends AbstractStep
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
     * @return MassEditTemporaryFileCleaner
     */
    public function getCleaner()
    {
        return $this->cleaner;
    }

    /**
     * @param MassEditTemporaryFileCleaner $cleaner
     *
     * @return MassEditRemoveTemporaryMediaStep
     */
    public function setCleaner(StepExecutionAwareInterface $cleaner)
    {
        $this->cleaner = $cleaner;

        return $this;
    }
}
