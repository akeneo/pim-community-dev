<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\UniqueIdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\UniqueIdentifierGeneratorCodeValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueIdentifierGeneratorCodeValidatorSpec extends ObjectBehavior
{
    public function let(IdentifierGeneratorRepository $repository, ExecutionContext $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(UniqueIdentifierGeneratorCodeValidator::class);
    }

    public function it_only_validates_unique_identifier_code_constraints(): void
    {
        $this->shouldThrow(
            \InvalidArgumentException::class
        )->during('validate', ['test', new NotBlank()]);
    }

    public function it_only_validates_a_string(
        IdentifierGeneratorRepository $repository,
        ExecutionContext $context
    ): void {
        $repository->get(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new UniqueIdentifierGeneratorCode());
    }

    public function it_does_not_add_a_violations_if_the_code_does_not_exist(
        IdentifierGeneratorRepository $repository,
        ExecutionContext $context
    ): void {
        $repository
            ->get('new_identifier_code')
            ->shouldBeCalled()
            ->willThrow(new CouldNotFindIdentifierGeneratorException('new_identifier_code'));
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('new_identifier_code', new UniqueIdentifierGeneratorCode());
    }

    public function it_adds_a_a_violation_if_the_code_is_already_used(
        IdentifierGeneratorRepository $repository,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationsBuilder
    ): void {
        $constraint = new UniqueIdentifierGeneratorCode();
        $repository->get('existing_code')->shouldBeCalled()->willReturn(
            new IdentifierGenerator(
                IdentifierGeneratorId::fromString('fdf0a55e-0337-4f2c-93f5-c2de84353ea2'),
                IdentifierGeneratorCode::fromString('existing_code'),
                Conditions::fromArray([new EmptyIdentifier('sku')]),
                Structure::fromArray([new AutoNumber(1, 4)]),
                LabelCollection::fromNormalized([]),
                Target::fromString('sku'),
                Delimiter::fromString('-'),
                TextTransformation::fromString(TextTransformation::NO)
            )
        );
        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($violationsBuilder);
        $violationsBuilder->addViolation()->shouldBeCalled()->willReturn($violationsBuilder);

        $this->validate('existing_code', $constraint);
    }
}
