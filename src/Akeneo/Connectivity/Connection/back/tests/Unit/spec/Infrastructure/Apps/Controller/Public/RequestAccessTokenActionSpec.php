<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestAccessTokenActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        ValidatorInterface $validator,
        CreateAccessTokenInterface $createAccessToken,
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $validator,
            $createAccessToken,
        );
    }

    public function it_throws_not_found_exception_with_feature_flag_disabled(
        FeatureFlag $featureFlag,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_returns_a_bad_request_response_when_access_token_request_is_invalid(
        FeatureFlag $featureFlag,
        Request $request,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList,
        ConstraintViolationInterface $constraintViolation,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);

        $request->request = new InputBag([
            'client_id' => 'some_client_id',
            'code' => 'some_code',
            'grant_type' => 'some_grant_type',
            'code_identifier' => 'some_code_identifier',
            'code_challenge' => 'some_code_challenge',
        ]);

        $constraintViolation->getMessage()->willReturn('invalid_grant');
        $constraintViolationList->count()->willReturn(1);
        $constraintViolationList->offsetGet(0)->willReturn($constraintViolation);
        $validator->validate(Argument::type(AccessTokenRequest::class))->willReturn($constraintViolationList);

        $this->__invoke($request)->shouldBeLike(new JsonResponse([
            'error' => 'invalid_grant'
        ], Response::HTTP_BAD_REQUEST));
    }

    public function it_returns_a_bad_request_response_with_error_description_when_code_challenge_is_expired(
        FeatureFlag $featureFlag,
        Request $request,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolationList,
        ConstraintViolation $constraintViolation,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);

        $request->request = new InputBag([
            'client_id' => 'some_client_id',
            'code' => 'some_code',
            'grant_type' => 'some_grant_type',
            'code_identifier' => 'some_code_identifier',
            'code_challenge' => 'some_code_challenge',
        ]);

        $constraintViolation->getMessage()->willReturn('invalid_grant');
        $constraintViolation->getCause()->willReturn('Code is expired');
        $constraintViolationList->count()->willReturn(1);
        $constraintViolationList->offsetGet(0)->willReturn($constraintViolation);
        $validator->validate(Argument::type(AccessTokenRequest::class))->willReturn($constraintViolationList);

        $this->__invoke($request)->shouldBeLike(new JsonResponse([
            'error' => 'invalid_grant',
            'error_description' => 'Code is expired',
        ], Response::HTTP_BAD_REQUEST));
    }
}
