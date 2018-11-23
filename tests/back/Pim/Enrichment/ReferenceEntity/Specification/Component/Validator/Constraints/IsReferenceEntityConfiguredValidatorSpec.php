<?php

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\IsReferenceEntityConfigured;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints\IsReferenceEntityConfiguredValidator;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsReferenceEntityConfiguredValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, FindReferenceEntityDetailsInterface $findReferenceEntityDetails)
    {
        $this->beConstructedWith(
            ['akeneo_reference_entity_collection'],
            $findReferenceEntityDetails
        );
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsReferenceEntityConfiguredValidator::class);
    }

    function it_builds_violation_for_a_null_reference_entity_collection(
        $context,
        Attribute $attribute,
        IsReferenceEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getReferenceDataName()->willReturn(null);

        $context->buildViolation($constraint->emptyMessage)->willReturn($violationBuilder);

        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_an_empty_reference_entity_collection(
        $context,
        Attribute $attribute,
        IsReferenceEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getReferenceDataName()->willReturn('');

        $context->buildViolation($constraint->emptyMessage)->willReturn($violationBuilder);

        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_an_invalid_reference_entity_collection(
        $context,
        Attribute $attribute,
        IsReferenceEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getReferenceDataName()->willReturn('//designer');

        $context->buildViolation($constraint->invalidMessage)->willReturn($violationBuilder);

        $violationBuilder->setParameter('%reference_entity_identifier%', '//designer')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_an_unknown_reference_entity_collection(
        $findReferenceEntityDetails,
        $context,
        Attribute $attribute,
        IsReferenceEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getReferenceDataName()->willReturn('designer');
        $findReferenceEntityDetails->__invoke('designer')->willReturn(null);

        $context->buildViolation($constraint->unknownMessage)->willReturn($violationBuilder);
        $violationBuilder->setParameter('%reference_entity_identifier%', 'designer')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_builds_violation_for_a_valid_reference_entity_collection(
        $findReferenceEntityDetails,
        $context,
        Attribute $attribute,
        IsReferenceEntityConfigured $constraint,
        ReferenceEntityDetails $designer
    ) {
        $attribute->getType()->willReturn('akeneo_reference_entity_collection');
        $attribute->getReferenceDataName()->willReturn('designer');
        $findReferenceEntityDetails->__invoke('designer')->willReturn($designer);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }
}
