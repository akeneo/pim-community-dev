<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject\Flat;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository as BaseGroupRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\TransformBundle\Exception\MissingIdentifierException;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

class VariantGroupProcessorSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        NormalizerInterface $valueNormalizer,
        StepExecution $stepExecution
    ) {
        $templateClass = 'Pim\Bundle\CatalogBundle\Entity\ProductTemplate';
        $groupClass = 'Pim\Bundle\CatalogBundle\Entity\Group';
        $this->beConstructedWith(
            $groupRepository,
            $denormalizer,
            $validator,
            $valueNormalizer,
            $groupClass,
            $templateClass
        );
        $this->setStepExecution($stepExecution);
        $validator->validate(Argument::any())->willReturn(new ConstraintViolationList());
    }

    function it_is_a_configurable_step_execution_aware_writer()
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
        $groupRepository->getReferenceProperties()->willReturn(['code']);
        $this
            ->shouldThrow(new MissingIdentifierException('No identifier column.'))
            ->duringProcess([]);
    }

    function it_throws_exception_when_try_to_update_standard_group($groupRepository, Group $group, GroupType $crossSell)
    {
        $groupRepository->getReferenceProperties()->willReturn(['code']);
        $groupRepository->findByReference('bar')->willReturn($group);
        $group->getId()->willReturn(42);
        $group->getType()->willReturn($crossSell);
        $crossSell->isVariant()->willReturn(false);
        $this
            ->shouldThrow(
                new InvalidItemException(
                    'Variant group "bar" does not exist',
                    ['code' => 'bar']
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
        $groupRepository->getReferenceProperties()->willReturn(['code']);
        $groupRepository->findByReference('tshirt')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $denormalizer->denormalize(
            [
                'code' => 'tshirt',
                'axis' => 'color',
                'label-en_US' => 'Tshirt',
                'type' => 'VARIANT'
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
                'code' => 'tshirt',
                'axis' => 'color',
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
        $groupRepository->getReferenceProperties()->willReturn(['code']);
        $groupRepository->findByReference('tshirt')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $denormalizer->denormalize(
            [
                'code' => 'tshirt',
                'axis' => 'color',
                'label-en_US' => 'Tshirt',
                'type' => 'VARIANT'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $variantGroup]
        )->shouldBeCalled()->willReturn($variantGroup);

        $variantGroup->getProductTemplate()->willReturn($template);

        $denormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->shouldBeCalled()
            ->willReturn([$value]);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');
        $validator->validate($value)->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $valueNormalizer
            ->normalize($value, 'json', ['entity' => 'product'])
            ->shouldBeCalled()
            ->willReturn(
                [
                    'scope'  => null,
                    'locale' => null,
                    'value'  => 'Nice product'
                ]
            );

        $variantGroup->getProductTemplate()->willReturn($template);
        $template
            ->setValuesData(
                [
                    'name' => [
                        ['scope'  => null, 'locale' => null, 'value'  => 'Nice product']
                    ]
                ]
            )
            ->shouldBeCalled();

        $validator
            ->validate($variantGroup)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $this->process(
            [
                'code' => 'tshirt',
                'axis' => 'color',
                'label-en_US' => 'Tshirt',
                'name' => 'Nice product'
            ]
        );
    }

    function it_updates_a_variant_group_and_skip_invalid_values(
        $groupRepository,
        $denormalizer,
        $validator,
        $valueNormalizer,
        $stepExecution,
        Group $variantGroup,
        GroupType $type,
        ProductTemplateInterface $template,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $groupRepository->getReferenceProperties()->willReturn(['code']);
        $groupRepository->findByReference('tshirt')->willReturn($variantGroup);
        $variantGroup->getId()->willReturn(42);
        $variantGroup->getType()->willReturn($type);
        $type->isVariant()->willReturn(true);

        $denormalizer->denormalize(
            [
                'code' => 'tshirt',
                'axis' => 'color',
                'label-en_US' => 'Tshirt',
                'type' => 'VARIANT'
            ],
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'csv',
            ['entity' => $variantGroup]
        )->shouldBeCalled()->willReturn($variantGroup);

        $variantGroup->getProductTemplate()->willReturn($template);

        $denormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->shouldBeCalled()
            ->willReturn([$value]);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $violation = new ConstraintViolation('There is a small problem', 'foo', [], 'bar', 'baz', 'Nice product');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($value)->shouldBeCalled()->willReturn($violations);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow(
                new InvalidItemException(
                    'There is a small problem: Nice product',
                    [
                        'code' => 'tshirt',
                        'axis' => 'color',
                        'label-en_US' => 'Tshirt',
                        'name' => 'Nice product'
                    ]
                )
            )
            ->duringProcess(
                [
                    'code' => 'tshirt',
                    'axis' => 'color',
                    'label-en_US' => 'Tshirt',
                    'name' => 'Nice product'
                ]
            );
    }
}

class GroupRepository extends BaseGroupRepository
{
    public function findOneByCode($code)
    {
        return $this->findOneBy(['code' => $code]);
    }
}
