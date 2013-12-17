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

// TODO : complete StepInterface, today the factory can't work with due to job repository and event dispatcher

class MyStep extends AbstractStep
{
    protected function doExecute(StepExecution $stepExecution)
    {
        die ('hell yeah');
    }

    public function getConfiguration()
    {
        return array();
    }

    public function setConfiguration(array $config)
    {
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
