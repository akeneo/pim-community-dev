<?php

namespace Pim\Bundle\BaseConnectorBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Pim\Bundle\EnrichBundle\MassEditAction\Cleaner\EditCommonAttributesTemporaryFileCleaner;

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

    /** @var EditCommonAttributesTemporaryFileCleaner */
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
     * @return EditCommonAttributesTemporaryFileCleaner
     */
    public function getCleaner()
    {
        return $this->cleaner;
    }

    /**
     * @param EditCommonAttributesTemporaryFileCleaner $cleaner
     *
     * @return MassEditRemoveTemporaryMedia
     */
    public function setCleaner(EditCommonAttributesTemporaryFileCleaner $cleaner)
    {
        $this->cleaner = $cleaner;

        return $this;
    }
}
