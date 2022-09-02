<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\ReferenceEntityExists;
use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\Feature;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ReferenceEntityIdentifierShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\ReferenceEntityIdentifierShouldExistValidator;
use Akeneo\Test\Pim\TableAttribute\Helper\FeatureHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;

final class ReferenceEntityIdentifierShouldExistValidatorSpec extends ObjectBehavior
{
    function let(ReferenceEntityExists $referenceEntityExists, Feature $feature, ExecutionContext $context)
    {
        $referenceEntityExists->forIdentifier('brand')
            ->willReturn(true);
        $referenceEntityExists->forIdentifier('unknown')
            ->willReturn(false);
        $this->beConstructedWith($referenceEntityExists, $feature);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ReferenceEntityIdentifierShouldExistValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            ['brand', new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_a_string(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(true, new ReferenceEntityIdentifierShouldExist());
    }

    function it_does_nothing_when_the_feature_is_not_enabled(Feature $feature, ExecutionContext $context)
    {
        $feature->isEnabled(Feature::REFERENCE_ENTITY)->willReturn(false);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(true, new ReferenceEntityIdentifierShouldExist());
    }

    function it_does_not_add_violation_when_reference_entity_exists(Feature $feature, ExecutionContext $context)
    {
        $feature->isEnabled(Feature::REFERENCE_ENTITY)->willReturn(true);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('brand', new ReferenceEntityIdentifierShouldExist());
    }

    function it_adds_violation_when_reference_entity_does_not_exist(Feature $feature, ExecutionContext $context)
    {
        $feature->isEnabled(Feature::REFERENCE_ENTITY)->willReturn(true);
        $constraint = new ReferenceEntityIdentifierShouldExist();
        $context->buildViolation($constraint->message, ['{{ reference_entity_identifier }}' => 'unknown'])
            ->shouldBeCalledOnce();
        $this->validate('unknown', $constraint);
    }
}
