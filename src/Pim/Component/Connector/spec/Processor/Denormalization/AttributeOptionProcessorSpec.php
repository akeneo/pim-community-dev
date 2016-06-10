<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttributeOptionProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $optionRepository,
        ObjectUpdaterInterface $optionUpdater,
        ValidatorInterface $optionValidator,
        StepExecution $stepExecution
    ) {
        $optionClass = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
        $this->beConstructedWith(
            $optionRepository,
            $optionUpdater,
            $optionValidator,
            $optionClass
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_attribute_option(
        $optionRepository,
        $optionUpdater,
        $optionValidator,
        AttributeOptionInterface $option,
        ConstraintViolationListInterface $violationList
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn($option);
        $option->getId()->willReturn(42);

        $optionUpdater
            ->update($option, ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldBeCalled();

        $option->getAttribute()->willReturn(null);
        $optionValidator
            ->validate($option)
            ->willReturn($violationList);

        $this
            ->process(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldReturn($option);
    }

    function it_creates_an_attribute_option(
        $optionRepository,
        $optionUpdater,
        $optionValidator,
        ConstraintViolationListInterface $violationList
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn(null);

        $optionUpdater
            ->update(Argument::any(), ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldBeCalled();

        $optionValidator
            ->validate(Argument::any())
            ->willReturn($violationList);

        $this
            ->process(['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeOption');
    }

    function it_skips_an_attribute_option_when_update_fails(
        $optionRepository,
        $optionUpdater
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn(null);

        $optionUpdater
            ->update(Argument::any(), ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->willThrow(new \InvalidArgumentException('attribute does not exists'));

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12]]
            );
    }

    function it_skips_an_attribute_option_when_object_is_invalid(
        $optionRepository,
        $optionUpdater,
        $optionValidator,
        AttributeOptionInterface $option
    ) {
        $optionRepository->getIdentifierProperties()->willReturn(['attribute', 'code']);
        $optionRepository->findOneByIdentifier(Argument::any())->willReturn($option);

        $optionUpdater
            ->update(Argument::any(), ['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12])
            ->shouldBeCalled();

        $violation = new ConstraintViolation('There is a small problem with option code', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $optionValidator
            ->validate($option)
            ->willReturn($violations);

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [['attribute' => 'myattribute', 'code' => 'mycode', 'sort_order' => 12]]
            );
    }
}
