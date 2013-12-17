<?php 

namespace Pim\Bundle\TestConnectorBundle\Step;

use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\BatchBundle\Step\AbstractStep;
use Oro\Bundle\BatchBundle\Step\StepInterface;

use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

use Pim\Bundle\CatalogBundle\Entity\Association;

// TODO : complete StepInterface, today the factory can't work with due to job repository and event dispatcher

class MyStep extends AbstractStep
{
    protected $config;

    protected function doExecute(StepExecution $stepExecution)
    {
        $assoc = new Association();
        $assoc->setCode('My name');
        $this->serializer->setStepExecution($stepExecution);
        $output = $this->serializer->process($assoc);

        echo $output;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function setConfiguration(array $config)
    {
        $this->config = $config;
    }

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    public function setMyParam($myparam)
    {
        $this->myparam = $myparam;
    }
}
