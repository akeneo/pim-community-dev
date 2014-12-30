<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

class VariantGroupValuesProcessorSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
        DenormalizerInterface $valueDenormalizer,
        ValidatorInterface $valueValidator,
        ObjectDetacherInterface $detacher,
        StepExecution $stepExecution
    ) {
        $templateClass = 'Pim\Bundle\CatalogBundle\Entity\ProductTemplate';
        $this->beConstructedWith(
            $groupRepository,
            $valueDenormalizer,
            $valueValidator,
            $detacher,
            $templateClass
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_writer()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }
}
