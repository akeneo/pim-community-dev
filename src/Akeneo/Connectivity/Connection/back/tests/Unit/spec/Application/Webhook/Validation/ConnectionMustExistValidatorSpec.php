<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUniqueValidator;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ConnectionMustExist;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ConnectionMustExistValidator;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionMustExistValidatorSpec extends ObjectBehavior
{
    public function let(ConnectionRepository $repository, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($repository);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConnectionMustExistValidator::class);
    }

    public function it_is_a_constraint_validator(): void
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_validates_that_a_connection_must_exist($repository, $context): void
    {
        $constraint = new ConnectionMustExist();
        $magento = new Connection(
            'magento',
            'Magento connector',
            FlowType::DATA_DESTINATION,
            42,
            50,
            null,
            true
        );
        $repository->findOneByCode('magento')->willReturn($magento);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('magento', $constraint);
    }

    public function it_build_a_violation_if_the_connection_does_not_exist(
        $repository,
        $context,
        ConstraintViolationBuilderInterface $builder
    ): void {
        $constraint = new ConnectionMustExist();
        $repository
            ->findOneByCode('magento')
            ->willReturn(null);

        $context
            ->buildViolation('akeneo_connectivity.connection.webhook.error.not_found')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate('magento', $constraint);
    }
}
