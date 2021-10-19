<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\CodeChallengeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CodeChallengeMustBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        WebMarketplaceApiInterface $webMarketplaceApi,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($webMarketplaceApi);
        $this->initialize($context);
    }

    public function it_throw_if_not_the_excepted_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_throw_if_not_the_excepted_value_class(
        CodeChallengeMustBeValid $constraint
    ): void {
        $this->shouldThrow(\LogicException::class)->during('validate', [
            new \stdClass(),
            $constraint,
        ]);
    }

    public function it_validate_that_the_code_challenge_is_valid(
        CodeChallengeMustBeValid $constraint,
        AccessTokenRequest $value,
        WebMarketplaceApiInterface $webMarketplaceApi,
        ExecutionContextInterface $context
    ): void {
        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';

        $value->getClientId()->willReturn($clientId);
        $value->getCodeIdentifier()->willReturn($codeIdentifier);
        $value->getCodeChallenge()->willReturn($codeChallenge);

        $webMarketplaceApi->validateCodeChallenge(
            $clientId,
            $codeIdentifier,
            $codeChallenge
        )->willReturn(true);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_when_the_code_challenge_is_refused(
        CodeChallengeMustBeValid $constraint,
        AccessTokenRequest $value,
        WebMarketplaceApiInterface $webMarketplaceApi,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';

        $value->getClientId()->willReturn($clientId);
        $value->getCodeIdentifier()->willReturn($codeIdentifier);
        $value->getCodeChallenge()->willReturn($codeChallenge);

        $webMarketplaceApi->validateCodeChallenge(
            $clientId,
            $codeIdentifier,
            $codeChallenge
        )->willReturn(false);

        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }
}
