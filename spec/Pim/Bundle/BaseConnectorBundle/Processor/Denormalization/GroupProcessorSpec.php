<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupProcessorSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        StepExecution $stepExecution
    ) {
        $groupClass = 'Pim\Bundle\CatalogBundle\Entity\Group';
        $this->beConstructedWith(
            $groupRepository,
            $denormalizer,
            $validator,
            $detacher,
            $groupClass,
            'csv'
        );
        $this->setStepExecution($stepExecution);
        $validator->validate(Argument::any())->willReturn(new ConstraintViolationList());
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_requires_group_code_in_the_data($groupRepository)
    {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $this
            ->shouldThrow(new InvalidItemException('Code must be provided', []))
            ->duringProcess([]);
    }

    function it_throws_exception_when_try_to_update_variant_group($groupRepository, Group $variantGroup, GroupType $variant)
    {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('bar')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($variant);
        $variant->isVariant()->willReturn(true);
        $this
            ->shouldThrow(
                new InvalidItemException(
                    'Cannot process variant group "bar", only groups are accepted',
                    ['code' => 'bar']
                )
            )
            ->duringProcess(['code' => 'bar']);
    }

    function it_updates_group(
        $groupRepository,
        $denormalizer,
        $validator,
        Group $group,
        GroupType $type
    ) {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('akeneo_xsell')->willReturn($group);
        $group->getId()->willReturn(42);
        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);

        $denormalizer->denormalize(
            [
                'code' => 'akeneo_xsell',
                'label-en_US' => 'Akeneo XSELL',
                'type' => 'XSELL'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $group]
        )->shouldBeCalled()->willReturn($group);

        $validator
            ->validate($group)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $this->process(
            [
                'code' => 'akeneo_xsell',
                'label-en_US' => 'Akeneo XSELL',
                'type' => 'XSELL'
            ]
        );
    }

    function it_creates_group(
        $groupRepository,
        $denormalizer,
        $validator,
        Group $group,
        GroupType $type
    ) {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('akeneo_xsell')->willReturn($group);
        $group->getId()->willReturn(null);
        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);

        $denormalizer->denormalize(
            [
                'code' => 'akeneo_xsell',
                'label-en_US' => 'Akeneo XSELL',
                'type' => 'XSELL'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $group]
        )->shouldBeCalled()->willReturn($group);

        $validator
            ->validate($group)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $this->process(
            [
                'code' => 'akeneo_xsell',
                'label-en_US' => 'Akeneo XSELL',
                'type' => 'XSELL'
            ]
        );
    }

    function it_skip_group_when_data_is_invalid(
        $groupRepository,
        $denormalizer,
        $validator,
        $detacher,
        $stepExecution,
        Group $group,
        GroupType $type
    ) {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('akeneo xsell')->willReturn($group);
        $group->getId()->willReturn(null);
        $group->getType()->willReturn($type);
        $type->isVariant()->willReturn(false);

        $denormalizer->denormalize(
            [
                'code' => 'akeneo xsell',
                'label-en_US' => 'Akeneo XSELL',
                'type' => 'XSELL'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $group]
        )->shouldBeCalled()->willReturn($group);

        $violation = new ConstraintViolation('There is a small problem with group code', 'foo', [], 'bar', 'code', 'akeneo xsell');
        $violations = new ConstraintViolationList([$violation]);

        $validator
            ->validate($group)
            ->shouldBeCalled()
            ->willReturn($violations);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $detacher->detach($group)->shouldBeCalled();

        $this
            ->shouldThrow(
                new InvalidItemException(
                    "code: There is a small problem with group code: akeneo xsell\n",
                    [
                        'code' => 'akeneo xsell',
                        'label-en_US' => 'Akeneo XSELL',
                        'type' => 'XSELL'
                    ]
                )
            )
            ->duringProcess(
                [
                    'code' => 'akeneo xsell',
                    'label-en_US' => 'Akeneo XSELL',
                    'type' => 'XSELL'
                ]
            );
    }
}
