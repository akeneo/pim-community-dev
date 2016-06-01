<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\GroupTypeFactory;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupTypeProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $groupTypeConverter,
        GroupTypeFactory $groupTypeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $groupTypeConverter, $groupTypeFactory, $updater, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_group_type(
        $repository,
        $groupTypeConverter,
        $updater,
        $validator,
        GroupTypeInterface $groupType,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('variant')->willReturn($groupType);

        $groupType->getId()->willReturn(42);

        $values = $this->getValues();

        $groupTypeConverter->convert($values['original_values'])->willReturn($values['converted_values']);

        $updater->update($groupType, $values['converted_values'])->shouldBeCalled();

        $validator->validate($groupType)->willReturn($violationList);

        $this->process($values['original_values'])->shouldReturn($groupType);
    }

    function it_skips_an_attribute_value_when_update_fails(
        $repository,
        $groupTypeConverter,
        $updater,
        $validator,
        GroupTypeInterface $groupType,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('variant')->willReturn($groupType);

        $groupType->getId()->willReturn(42);

        $values = $this->getValues();

        $groupTypeConverter->convert($values['original_values'])->willReturn($values['converted_values']);

        $validator->validate($groupType)->willReturn($violationList);

        $updater->update($groupType, $values['converted_values'])->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during('process', [$values['original_values']]);
    }

    function it_skips_an_attribute_group_when_object_is_invalid(
        $repository,
        $groupTypeConverter,
        $validator,
        GroupTypeInterface $groupType
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('variant')->willReturn($groupType);

        $groupType->getId()->willReturn(42);

        $values = $this->getValues();

        $groupTypeConverter->convert($values['original_values'])->willReturn($values['converted_values']);

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'sizes');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($groupType)->willReturn($violations);

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function getValues()
    {
        return [
            'original_values' => [
                'code'        => 'variant',
                'is_variant'  => 1,
                'label-en_US' => 'variant',
                'label-fr_FR' => 'variantes'
            ],
            'converted_values' => [
                'code'       => 'variant',
                'sort_order' => true,
                'label'      => [
                    'en_US' => 'variant',
                    'fr_FR' => 'variantes'
                ]
            ]
        ];
    }
}
