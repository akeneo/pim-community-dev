<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\Validator\Constraints\IsReferenceDataConfigured;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsReferenceDataConfiguredValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, ConfigurationRegistryInterface $registry)
    {
        $this->beConstructedWith(
            ['pim_reference_data_multiselect', 'pim_reference_data_simpleselect'],
            $registry
        );
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Validator\Constraints\IsReferenceDataConfiguredValidator');
    }

    function it_builds_violation_for_non_configured_simpleselect_reference_data(
        $registry,
        $context,
        Attribute $attribute,
        IsReferenceDataConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('pim_reference_data_simpleselect');
        $attribute->getReferenceDataName()->willReturn('foo');
        $registry->has('foo')->willReturn(false);
        $registry->all()->willReturn(['bar' => 'bar']);

        $context->buildViolation($constraint->message)->willReturn($violationBuilder);

        $violationBuilder->setParameter('%reference_data_name%', 'foo')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setParameter('%references%', 'bar')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_build_violation_for_configured_simpleselect_reference_data(
        $registry,
        $context,
        Attribute $attribute,
        IsReferenceDataConfigured $constraint
    ) {
        $attribute->getType()->willReturn('pim_reference_data_simpleselect');
        $attribute->getReferenceDataName()->willReturn('foo');
        $registry->has('foo')->willReturn(true);

        $registry->all()->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_non_configured_multiselect_reference_data(
        $registry,
        $context,
        Attribute $attribute,
        IsReferenceDataConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('pim_reference_data_multiselect');
        $attribute->getReferenceDataName()->willReturn('foo');
        $registry->has('foo')->willReturn(false);
        $registry->all()->willReturn(['bar' => 'bar']);

        $context->buildViolation($constraint->message)->willReturn($violationBuilder);

        $violationBuilder->setParameter('%reference_data_name%', 'foo')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setParameter('%references%', 'bar')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_build_violation_for_configured_multiselect_reference_data(
        $registry,
        $context,
        Attribute $attribute,
        IsReferenceDataConfigured $constraint
    ) {
        $attribute->getType()->willReturn('pim_reference_data_multiselect');
        $attribute->getReferenceDataName()->willReturn('foo');
        $registry->has('foo')->willReturn(true);

        $registry->all()->shouldNotBeCalled();
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }
}
