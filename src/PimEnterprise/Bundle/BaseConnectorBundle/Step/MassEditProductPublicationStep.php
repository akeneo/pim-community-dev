<?php

namespace PimEnterprise\Bundle\BaseConnectorBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler\PublishProductHandler;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditProductPublicationStep extends AbstractStep
{
    /** @var array */
    protected $configuration;

    /** @var PublishProductHandler */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->handler->setStepExecution($stepExecution);
        $this->handler->execute($this->configuration);
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
     * @return PublishProductHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param PublishProductHandler $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
}
