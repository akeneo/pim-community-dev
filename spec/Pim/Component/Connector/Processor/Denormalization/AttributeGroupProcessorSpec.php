<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\AttributeGroupFactory;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttributeGroupProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $groupConverter,
        AttributeGroupFactory $groupFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($repository, $groupConverter, $groupFactory, $updater, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_updates_an_existing_attribute_group(
        $repository,
        $groupConverter,
        $updater,
        $validator,
        AttributeGroupInterface $attributeGroup,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sizes')->willReturn($attributeGroup);

        $attributeGroup->getId()->willReturn(42);

        $values = $this->getValues();

        $groupConverter->convert($values['original_values'])->willReturn($values['converted_values']);

        $updater->update($attributeGroup, $values['converted_values'])->shouldBeCalled();

        $validator->validate($attributeGroup)->willReturn($violationList);

        $this->process($values['original_values'])->shouldReturn($attributeGroup);
    }

    function it_skips_an_attribute_value_when_update_fails(
        $repository,
        $groupConverter,
        $updater,
        $validator,
        AttributeGroupInterface $attributeGroup,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sizes')->willReturn($attributeGroup);

        $attributeGroup->getId()->willReturn(42);

        $values = $this->getValues();

        $groupConverter->convert($values['original_values'])->willReturn($values['converted_values']);

        $validator->validate($attributeGroup)->willReturn($violationList);

        $updater->update($attributeGroup, $values['converted_values'])->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during('process', [$values['original_values']]);
    }

    function it_skips_an_attribute_group_when_object_is_invalid(
        $repository,
        $groupConverter,
        $validator,
        AttributeGroupInterface $attributeGroup
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('sizes')->willReturn($attributeGroup);

        $attributeGroup->getId()->willReturn(42);

        $values = $this->getValues();

        $groupConverter->convert($values['original_values'])->willReturn($values['converted_values']);

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'sizes');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($attributeGroup)->willReturn($violations);

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
                'code'        => 'sizes',
                'sort_order'  => 1,
                'attributes'  => 'size,main_color',
                'label-en_US' => 'Sizes',
                'label-fr_FR' => 'Tailles'
            ],
            'converted_values' => [
                'code'       => 'sizes',
                'sort_order' => 1,
                'attributes' => ['size', 'main_color'],
                'label'      => [
                    'en_US' => 'Sizes',
                    'fr_FR' => 'Tailles'
                ]
            ]
        ];
    }
}
