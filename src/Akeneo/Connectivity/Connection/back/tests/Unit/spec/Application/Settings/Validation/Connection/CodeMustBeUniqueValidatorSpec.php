<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUniqueValidator;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CodeMustBeUniqueValidatorSpec extends ObjectBehavior
{
    public function let(ConnectionRepository $repository, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CodeMustBeUniqueValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_validates_a_connection_code_must_be_unique($repository, $context): void
    {
        $constraint = new CodeMustBeUnique();
        $repository->findOneByCode('magento')->willReturn(null);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('magento', $constraint)->shouldReturn(null);
    }

    public function it_build_a_violation_if_the_code_is_not_unique(
        $repository,
        $context,
        ConstraintViolationBuilderInterface $builder
    ): void {
        $constraint = new CodeMustBeUnique();
        $repository
            ->findOneByCode('magento')
            ->willReturn(
                new Connection('magento', 'Magento connector', FlowType::DATA_DESTINATION, 42, new UserId(50))
            );

        $context->buildViolation('akeneo_connectivity.connection.connection.constraint.code.must_be_unique')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('magento', $constraint)->shouldReturn(null);
    }
}
