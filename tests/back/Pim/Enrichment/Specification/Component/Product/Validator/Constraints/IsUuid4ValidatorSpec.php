<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsUuid4;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsUuid4Validator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUuid4ValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(IsUuid4Validator::class);
    }

    function it_should_not_be_called_with_another_constraint()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [Uuid::uuid4(), new NotBlank()]);
    }

    function it_should_only_validate_uuids()
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [42, new IsUuid4()]);
    }

    function it_should_not_validate_null_value(
        ExecutionContextInterface $context
    ) {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();
        $this->validate(null, new IsUuid4());
    }

    function it_should_not_build_violation_with_uuid4(
        ExecutionContextInterface $context
    ) {
        $context
            ->buildViolation(Argument::any())
            ->shouldNotBeCalled();
        $this->validate(Uuid::uuid4(), new IsUuid4());
    }

    function it_should_build_violation_with_wrong_uuid_version(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $uuid = Uuid::uuid1();
        $context
            ->buildViolation(Argument::any())
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->setParameter('{{ uuid }}', $uuid->toString())
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->setParameter('{{ version }}', '1')
            ->shouldBeCalled()
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->addViolation()
            ->shouldBeCalled();
        $this->validate($uuid, new IsUuid4());
    }
}
