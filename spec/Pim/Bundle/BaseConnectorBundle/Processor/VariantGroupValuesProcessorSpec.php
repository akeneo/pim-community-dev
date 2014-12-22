<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Persistence\DetacherInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

class VariantGroupValuesProcessorSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
        StepExecution $stepExecution,
        DenormalizerInterface $valueDenormalizer,
        FieldNameBuilder $fieldNameBuilder,
        ValidatorInterface $valueValidator,
        DetacherInterface $detacher
    ) {
        $valueClass = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
        $templateClass = 'Pim\Bundle\CatalogBundle\Entity\ProductTemplate';
        $this->beConstructedWith(
            $groupRepository,
            $valueDenormalizer,
            $fieldNameBuilder,
            $valueValidator,
            $detacher,
            $valueClass,
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
