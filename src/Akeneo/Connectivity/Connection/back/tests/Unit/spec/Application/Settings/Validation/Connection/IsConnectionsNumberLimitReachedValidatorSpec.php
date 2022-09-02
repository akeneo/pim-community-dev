<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Query\IsConnectionsNumberLimitReachedHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\IsConnectionsNumberLimitReached;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\IsConnectionsNumberLimitReachedValidator;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedValidatorSpec extends ObjectBehavior
{
    public function let(IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($isConnectionsNumberLimitReachedQuery);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(IsConnectionsNumberLimitReachedValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_throws_on_wrong_constraint_type(Constraint $constraint): void
    {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', ['test', $constraint]);
    }

    public function it_validates_when_max_limit_is_not_reached(
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
        ExecutionContextInterface $context
    ): void {
        $isConnectionsNumberLimitReachedQuery->execute()->willReturn(false);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('test', new IsConnectionsNumberLimitReached());
    }

    public function it_builds_violations_when_max_limit_is_reached(
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $builder
    ): void {
        $constraint = new IsConnectionsNumberLimitReached();

        $isConnectionsNumberLimitReachedQuery->execute()->willReturn(true);

        $context->buildViolation($constraint->message)->shouldBeCalled()->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('test', $constraint);
    }
}
