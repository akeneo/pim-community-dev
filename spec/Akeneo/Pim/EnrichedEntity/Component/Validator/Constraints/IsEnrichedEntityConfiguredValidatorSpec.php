<?php

namespace spec\Akeneo\Pim\EnrichedEntity\Component\Validator\Constraints;

use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityDetailsInterface;
use Akeneo\Pim\EnrichedEntity\Component\Validator\Constraints\IsEnrichedEntityConfigured;
use Akeneo\Pim\EnrichedEntity\Component\Validator\Constraints\IsEnrichedEntityConfiguredValidator;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsEnrichedEntityConfiguredValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, FindEnrichedEntityDetailsInterface $findEnrichedEntityDetails)
    {
        $this->beConstructedWith(
            ['akeneo_enriched_entity_collection'],
            $findEnrichedEntityDetails
        );
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsEnrichedEntityConfiguredValidator::class);
    }

    function it_builds_violation_for_a_null_enriched_entity_collection(
        $context,
        Attribute $attribute,
        IsEnrichedEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getReferenceDataName()->willReturn(null);

        $context->buildViolation($constraint->emptyMessage)->willReturn($violationBuilder);

        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_an_empty_enriched_entity_collection(
        $context,
        Attribute $attribute,
        IsEnrichedEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getReferenceDataName()->willReturn('');

        $context->buildViolation($constraint->emptyMessage)->willReturn($violationBuilder);

        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_an_invalid_enriched_entity_collection(
        $context,
        Attribute $attribute,
        IsEnrichedEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getReferenceDataName()->willReturn('//designer');

        $context->buildViolation($constraint->invalidMessage)->willReturn($violationBuilder);

        $violationBuilder->setParameter('%enriched_entity_identifier%', '//designer')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_builds_violation_for_an_unknown_enriched_entity_collection(
        $findEnrichedEntityDetails,
        $context,
        Attribute $attribute,
        IsEnrichedEntityConfigured $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getReferenceDataName()->willReturn('designer');
        $findEnrichedEntityDetails->__invoke('designer')->willReturn(null);

        $context->buildViolation($constraint->unknownMessage)->willReturn($violationBuilder);
        $violationBuilder->setParameter('%enriched_entity_identifier%', 'designer')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('reference_data_name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_not_builds_violation_for_a_valid_enriched_entity_collection(
        $findEnrichedEntityDetails,
        $context,
        Attribute $attribute,
        IsEnrichedEntityConfigured $constraint,
        EnrichedEntityDetails $designer
    ) {
        $attribute->getType()->willReturn('akeneo_enriched_entity_collection');
        $attribute->getReferenceDataName()->willReturn('designer');
        $findEnrichedEntityDetails->__invoke('designer')->willReturn($designer);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }
}
