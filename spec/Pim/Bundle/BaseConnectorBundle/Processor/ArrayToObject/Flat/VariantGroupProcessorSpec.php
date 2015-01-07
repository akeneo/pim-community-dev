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
        DenormalizerInterface $valueDenormalizer,
        ValidatorInterface $validator,
        NormalizerInterface $valueNormalizer,
        StepExecution $stepExecution,
        Group $variant,
        GroupType $variantType
    ) {
        $templateClass = 'Pim\Bundle\CatalogBundle\Entity\ProductTemplate';
        $this->beConstructedWith(
            $groupRepository,
            $valueDenormalizer,
            $validator,
            $valueNormalizer,
            $templateClass
        );
        $this->setStepExecution($stepExecution);

        $groupRepository->findOneByCode('variant')->willReturn($variant);
        $variant->getType()->willReturn($variantType);
        $variantType->isVariant()->willReturn(true);
        $variant->getProductTemplate()->willReturn([]);
        $variant
            ->setProductTemplate(Argument::type('Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface'))
            ->willReturn($variant);

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

    function it_requires_variant_group_code_in_the_data()
    {
        $this
            ->shouldThrow(new \LogicException('Variant group code must be provided'))
            ->duringProcess([]);
    }

    function it_requires_an_existing_variant_group($groupRepository, Group $group, GroupType $crossSell)
    {
        $groupRepository->findOneByCode('foo')->willReturn(null);
        $groupRepository->findOneByCode('bar')->willReturn($group);

        $group->getType()->willReturn($crossSell);
        $crossSell->isVariant()->willReturn(false);

        $this
            ->shouldThrow(
                new InvalidItemException(
                    'Variant group "foo" does not exist',
                    ['variant_group_code' => 'foo']
                )
            )
            ->duringProcess(['variant_group_code' => 'foo']);
        $this
            ->shouldThrow(
                new InvalidItemException(
                    'Variant group "bar" does not exist',
                    ['variant_group_code' => 'bar']
                )
            )
            ->duringProcess(['variant_group_code' => 'bar']);
    }

    function it_denormalizes_the_passed_values_into_product_value_objects($valueDenormalizer)
    {
        $valueDenormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->shouldBeCalled()
            ->willReturn([]);

        $this->process(['variant_group_code' => 'variant', 'name' => 'Nice product']);
    }

    function it_validates_the_denormalized_values(
        $valueDenormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute,
        $validator
    ) {
        $valueDenormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->shouldBeCalled()
            ->willReturn([$value]);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $validator->validate($value)->shouldBeCalled()->willReturn(new ConstraintViolationList());

        $this->process(['variant_group_code' => 'variant', 'name' => 'Nice product']);
    }

    function it_skips_invalid_values(
        $valueDenormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute,
        $validator,
        $stepExecution
    ) {
        $valueDenormalizer
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
                    ['variant_group_code' => 'variant', 'name' => 'Nice product']
                )
            )
            ->duringProcess(['variant_group_code' => 'variant', 'name' => 'Nice product']);
    }

    function it_normalizes_the_values_into_json(
        $valueDenormalizer,
        $valueNormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $valueDenormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->shouldBeCalled()
            ->willReturn([$value]);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $valueNormalizer->normalize($value, 'json', ['entity' => 'product'])->shouldBeCalled();

        $this->process(['variant_group_code' => 'variant', 'name' => 'Nice product']);
    }

    function it_updates_group_template_with_the_normalized_data(
        $valueDenormalizer,
        $valueNormalizer,
        ProductValueInterface $value,
        AttributeInterface $attribute,
        $variant,
        ProductTemplateInterface $template
    ) {
        $valueDenormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->shouldBeCalled()
            ->willReturn([$value]);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

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

        $variant->getProductTemplate()->willReturn($template);
        $template
            ->setValuesData(
                [
                    'name' => [
                        ['scope'  => null, 'locale' => null, 'value'  => 'Nice product']
                    ]
                ]
            )
            ->shouldBeCalled();

        $this->process(['variant_group_code' => 'variant', 'name' => 'Nice product']);
    }

    function it_validates_the_variant_group($valueDenormalizer, $variant, $validator)
    {
        $valueDenormalizer
            ->denormalize(['name' => 'Nice product'], 'variant_group_values', 'csv')
            ->willReturn([]);

        $validator
            ->validate($variant)
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $this->process(['variant_group_code' => 'variant', 'name' => 'Nice product']);
    }
}

class GroupRepository extends BaseGroupRepository
{
    public function findOneByCode($code)
    {
        return $this->findOneBy(['code' => $code]);
    }
}
