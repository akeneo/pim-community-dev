<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\ClientIdMustBeValidValidator;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ClientIdMustBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ClientManagerInterface $clientManager,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($clientManager);
        $this->initialize($context);
    }

    public function it_is_an_app_authorization_session(): void
    {
        $this->shouldHaveType(ClientIdMustBeValidValidator::class);
    }

    public function it_throw_if_not_the_excepted_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_validate_that_the_client_id_exists(
        ClientIdMustBeValid $constraint,
        ClientManagerInterface $clientManager,
        ClientInterface $client,
        ExecutionContextInterface $context
    ): void {
        $clientManager->findClientBy(['marketplacePublicAppId' => 'app_id'])->willReturn($client);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('app_id', $constraint);
    }

    public function it_adds_a_violation_when_client_id_was_not_found(
        ClientIdMustBeValid $constraint,
        ClientManagerInterface $clientManager,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $clientManager->findClientBy(['marketplacePublicAppId' => 'app_id'])->willReturn(null);
        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate('app_id', $constraint);
    }
}
