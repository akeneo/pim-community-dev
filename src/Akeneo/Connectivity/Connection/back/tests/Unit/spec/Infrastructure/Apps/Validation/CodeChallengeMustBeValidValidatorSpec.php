<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\CodeChallengeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
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
        GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
        FeatureFlag $fakeAppsFeatureFlag,
        ExecutionContextInterface $context
    ): void {
        $this->beConstructedWith($webMarketplaceApi, $getCustomAppSecretQuery, $fakeAppsFeatureFlag);
        $fakeAppsFeatureFlag->isEnabled()->willReturn(false);
        $this->initialize($context);
    }

    public function it_validates_only_the_correct_constraint(
        Constraint $constraint
    ): void {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [
            null,
            $constraint,
        ]);
    }

    public function it_validates_only_an_access_token_request(
        CodeChallengeMustBeValid $constraint
    ): void {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [
            new \stdClass(),
            $constraint,
        ]);
    }

    public function it_validates_that_the_code_challenge_is_valid(
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

    public function it_skips_the_validator_if_a_value_is_empty(
        CodeChallengeMustBeValid $constraint,
        AccessTokenRequest $value,
        WebMarketplaceApiInterface $webMarketplaceApi,
        ExecutionContextInterface $context
    ): void {
        $value->getClientId()->willReturn('');
        $value->getCodeIdentifier()->willReturn('');
        $value->getCodeChallenge()->willReturn('');

        $webMarketplaceApi->validateCodeChallenge(Argument::cetera())->shouldNotBeCalled();

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
        $violation->atPath('codeChallenge')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_validates_that_the_custom_app_code_challenge_is_valid(
        CodeChallengeMustBeValid $constraint,
        AccessTokenRequest $value,
        GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
        ExecutionContextInterface $context
    ): void {
        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $clientSecret = 'nDYbJo8X48fL';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = '6ffbb306c0ce4a545d2540c9303c10258c4e4c321c3899c5177fd94106e1b73d';

        $value->getClientId()->willReturn($clientId);
        $value->getCodeIdentifier()->willReturn($codeIdentifier);
        $value->getCodeChallenge()->willReturn($codeChallenge);

        $getCustomAppSecretQuery->execute($clientId)->willReturn($clientSecret);

        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($value, $constraint);
    }

    public function it_adds_a_violation_when_the_custom_app_code_challenge_is_refused(
        CodeChallengeMustBeValid $constraint,
        AccessTokenRequest $value,
        GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violation
    ): void {
        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $clientSecret = 'nDYbJo8X48fL';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'invalid';

        $value->getClientId()->willReturn($clientId);
        $value->getCodeIdentifier()->willReturn($codeIdentifier);
        $value->getCodeChallenge()->willReturn($codeChallenge);

        $getCustomAppSecretQuery->execute($clientId)->willReturn($clientSecret);

        $context->buildViolation(Argument::any())->willReturn($violation);
        $violation->atPath('codeChallenge')->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($value, $constraint);
    }
}
