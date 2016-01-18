<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Pim\Bundle\TransformBundle\Exception\MissingIdentifierException;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        GroupRepositoryInterface $groupRepository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        NormalizerInterface $valueNormalizer,
        ObjectDetacherInterface $detacher,
        ProductTemplateMediaManager $templateMediaManager,
        FieldNameBuilder $fieldNameBuilder,
        StepExecution $stepExecution
    ) {
        $templateClass = 'Pim\Bundle\CatalogBundle\Entity\ProductTemplate';
        $groupClass    = 'Pim\Bundle\CatalogBundle\Entity\Group';
        $this->beConstructedWith(
            $groupRepository,
            $denormalizer,
            $validator,
            $detacher,
            $valueNormalizer,
            $templateMediaManager,
            $fieldNameBuilder,
            $groupClass,
            $templateClass,
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

    function it_requires_variant_group_code_in_the_data($groupRepository)
    {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $this
            ->shouldThrow(new MissingIdentifierException('No identifier column.'))
            ->duringProcess([]);
    }

    function it_throws_exception_when_try_to_update_standard_group($groupRepository, Group $group, GroupType $crossSell)
    {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('bar')->willReturn($group);
        $group->getId()->willReturn(42);
        $group->getType()->willReturn($crossSell);
        $crossSell->isVariant()->willReturn(false);
        $this
            ->shouldThrow(
                new InvalidItemException(
                    'Cannot process group "bar", only variant groups are accepted',
                    ['code' => 'bar', 'type' => 'VARIANT']
                )
            )
            ->duringProcess(['code' => 'bar']);
    }

    function it_updates_a_variant_group_fields(
        $groupRepository,
        $denormalizer,
        $validator,
        Group $variantGroup,
        GroupType $type
    ) {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('tshirt')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $denormalizer->denormalize(
            [
                'code'        => 'tshirt',
                'axis'        => 'color',
                'label-en_US' => 'Tshirt',
                'type'        => 'VARIANT'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $variantGroup]
        )->shouldBeCalled()->willReturn($variantGroup);

        $validator
            ->validate($variantGroup)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $this->process(
            [
                'code'        => 'tshirt',
                'axis'        => 'color',
                'label-en_US' => 'Tshirt'
            ]
        );
    }

    function it_updates_a_variant_group_and_its_values(
        $groupRepository,
        $denormalizer,
        $validator,
        $valueNormalizer,
        Group $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('tshirt')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $denormalizer->denormalize(
            [
                'code'        => 'tshirt',
                'axis'        => 'color',
                'label-en_US' => 'Tshirt',
                'type'        => 'VARIANT'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $variantGroup]
        )->willReturn($variantGroup);

        $variantGroup->getProductTemplate()->willReturn($template);

        $newValues = new ArrayCollection([$value]);

        $denormalizer
            ->denormalize(['name' => 'Nice product'], 'ProductValue[]', 'csv')
            ->willReturn($newValues);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');
        $validator->validate($value)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $variantGroup->getProductTemplate()->willReturn($template);

        $template->getValues()->willReturn($newValues);

        $template
            ->setValues($newValues)
            ->shouldBeCalled();

        $valueNormalizer
            ->normalize($newValues, 'json', ['entity' => 'product'])
            ->willReturn(
                [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'value' => 'Nice product']
                    ]
                ]
            );

        $template
            ->setValuesData(
                [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'value' => 'Nice product']
                    ]
                ]
            )
            ->shouldBeCalled();

        $template
            ->getValuesData()->willReturn(
                [
                    'name' => [
                        ['scope' => null, 'locale' => null, 'value' => 'Nice product']
                    ]
                ]
            );

        $validator
            ->validate($variantGroup)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $this->process(
            [
                'code'        => 'tshirt',
                'axis'        => 'color',
                'label-en_US' => 'Tshirt',
                'name'        => 'Nice product'
            ]
        );
    }

    function it_updates_a_variant_group_and_skip_invalid_values(
        $groupRepository,
        $denormalizer,
        $validator,
        $stepExecution,
        Group $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $groupRepository->getIdentifierProperties()->willReturn(['code']);
        $groupRepository->findOneByIdentifier('tshirt')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $denormalizer->denormalize(
            [
                'code'        => 'tshirt',
                'axis'        => 'color',
                'label-en_US' => 'Tshirt',
                'type'        => 'VARIANT'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $variantGroup]
        )->shouldBeCalled()->willReturn($variantGroup);

        $template->getValuesData()->willReturn([]);
        $variantGroup->getProductTemplate()->willReturn($template);

        $denormalizer
            ->denormalize(['name' => 'Nice product'], 'ProductValue[]', 'csv')
            ->shouldBeCalled()
            ->willReturn(new ArrayCollection([$value]));

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $violation  = new ConstraintViolation('There is a small problem', 'foo', [], 'bar', 'name', 'Nice product');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($value)->shouldBeCalled()->willReturn($violations);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow(
                new InvalidItemException(
                    "name: There is a small problem: Nice product\n",
                    [
                        'code'        => 'tshirt',
                        'axis'        => 'color',
                        'label-en_US' => 'Tshirt',
                        'name'        => 'Nice product',
                        'type'        => 'VARIANT'
                    ]
                )
            )
            ->duringProcess(
                [
                    'code'        => 'tshirt',
                    'axis'        => 'color',
                    'label-en_US' => 'Tshirt',
                    'name'        => 'Nice product'
                ]
            );
    }
}
