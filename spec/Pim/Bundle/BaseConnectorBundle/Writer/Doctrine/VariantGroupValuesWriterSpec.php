<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\TransformBundle\Cache\CacheClearer;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Resource\Model\SaverInterface;
use Prophecy\Argument;

class VariantGroupValuesWriterSpec extends ObjectBehavior
{
    function let(SaverInterface $groupSaver, CacheClearer $cacheClearer, StepExecution $stepExecution)
    { 
        $this->beConstructedWith($groupSaver, $cacheClearer);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_writer()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }
}
