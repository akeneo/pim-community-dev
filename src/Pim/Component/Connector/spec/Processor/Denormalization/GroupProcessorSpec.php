<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupProcessorSpec extends ObjectBehavior
{
    function let(
        ArrayConverterInterface $groupConverter,
        IdentifiableObjectRepositoryInterface $repository,
        GroupFactory $groupFactory,
        ObjectUpdaterInterface $groupUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($groupConverter, $repository, $groupFactory, $groupUpdater, $validator);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_group(
        $groupConverter,
        $repository,
        $groupUpdater,
        $validator,
        GroupInterface $group,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier('mycode')->willReturn($group);

        $groupType = new GroupType();
        $groupType->setVariant(false);

        $group->getType()->willReturn($groupType);
        $group->getId()->willReturn(42);
        $group->getProductTemplate()->willReturn(null);

        $values = $this->getValues();

        $groupConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $groupUpdater
            ->update($group, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($group)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($group);
    }

    function it_skips_a_group_when_update_fails(
        $groupConverter,
        $repository,
        $groupUpdater,
        $validator,
        GroupInterface $group,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($group);
        $groupType = new GroupType();
        $groupType->setVariant(false);

        $group->getType()->willReturn($groupType);
        $group->getId()->willReturn(42);
        $group->getProductTemplate()->willReturn(null);

        $values = $this->getValues();

        $groupConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $groupUpdater
            ->update($group, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($group)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($group);

        $groupUpdater
            ->update($group, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_group_when_object_is_invalid(
        $groupConverter,
        $repository,
        $groupUpdater,
        $validator,
        GroupInterface $group,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['code']);
        $repository->findOneByIdentifier(Argument::any())->willReturn($group);

        $groupType = new GroupType();
        $groupType->setVariant(false);

        $group->getType()->willReturn($groupType);
        $group->getId()->willReturn(42);
        $group->getProductTemplate()->willReturn(null);

        $values = $this->getValues();

        $groupConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $groupUpdater
            ->update($group, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($group)
            ->willReturn($violationList);

        $this
            ->process($values['original_values'])
            ->shouldReturn($group);

        $groupUpdater
            ->update($group, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException());

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($group)
            ->willReturn($violations);

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
                'code'                       => 'mycode',
                'type'                       => 'group',
                'label-fr_FR'                => 'T-shirt super beau',
                'label-en_US'                => 'T-shirt very beautiful',
            ],
            'converted_values' => [
                'code'         => 'mycode',
                'type'         => 'group',
                'labels'       => [
                    'fr_FR' => 'T-shirt super beau',
                    'en_US' => 'T-shirt very beautiful',
                ],
            ]
        ];
    }
}
