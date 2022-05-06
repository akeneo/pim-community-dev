<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppSecretQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\Internal\CreateTestAppAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateTestAppActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        CreateTestAppCommandHandler $createTestAppCommandHandler,
        GetTestAppSecretQueryInterface $getTestAppSecretQuery,
        SecurityFacade $security,
    ): void {
        $this->beConstructedWith(
            $appDevFeatureFlag,
            $activateFeatureFlag,
            $validator,
            $tokenStorage,
            $createTestAppCommandHandler,
            $getTestAppSecretQuery,
            $security,
        );
    }

    public function it_is_a_create_test_app_action(): void
    {
        $this->shouldHaveType(CreateTestAppAction::class);
    }

    public function it_answers_that_the_entity_has_not_been_created_because_the_secret_can_not_be_retrieved(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        CreateTestAppCommandHandler $createTestAppCommandHandler,
        GetTestAppSecretQueryInterface $getTestAppSecretQuery,
        SecurityFacade $security,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(42);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $request->get('name', '')->willReturn('Test app name');
        $request->get('callbackUrl', '')->willReturn('http://callback-url.test');
        $request->get('activateUrl', '')->willReturn('http://callback-url.test');

        $constraintList = new ConstraintViolationList([]);
        $validator->validate(Argument::type(CreateTestAppCommand::class))->willReturn($constraintList);
        $createTestAppCommandHandler->handle(Argument::type(CreateTestAppCommand::class))->shouldBeCalled();
        $getTestAppSecretQuery->execute(Argument::type('string'))->willReturn(null);

        $this->__invoke($request)->shouldBeLike(new JsonResponse(
            ['errors' => ['propertyPath' => null, 'message' => 'The client secret can not be retrieved.']],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }

    public function it_answers_that_the_endpoint_does_not_exist_if_the_dev_mode_feature_flag_is_disabled(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(false);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request]);
    }

    public function it_answers_that_the_endpoint_does_not_exist_if_the_activate_feature_flag_is_disabled(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
    ): void {
        $activateFeatureFlag->isEnabled()->willReturn(false);
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request]);
    }

    public function it_redirects_to_the_root_if_the_request_does_not_come_from_ajax(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(false);

        $this->__invoke($request)->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_answers_an_access_denied_if_the_endpoint_is_not_granted_to_the_user(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
        SecurityFacade $security,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request]);
    }

    public function it_answers_that_a_bad_request_has_been_done_because_the_token_does_not_exist(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
        TokenStorageInterface $tokenStorage,
        SecurityFacade $security,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $tokenStorage->getToken()->willReturn(null);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $this->__invoke($request)->shouldBeLike(new JsonResponse(
            'Invalid user token.',
            Response::HTTP_BAD_REQUEST,
        ));
    }

    public function it_answers_that_a_bad_request_has_been_done_because_the_user_does_not_exist(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        SecurityFacade $security,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn(null);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $this->__invoke($request)->shouldBeLike(new JsonResponse(
            'Invalid user token.',
            Response::HTTP_BAD_REQUEST,
        ));
    }

    public function it_answers_that_the_entity_is_unprocessable_with_details_if_the_command_is_not_valid(
        FeatureFlag $appDevFeatureFlag,
        FeatureFlag $activateFeatureFlag,
        Request $request,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        SecurityFacade $security,
    ): void {
        $appDevFeatureFlag->isEnabled()->willReturn(true);
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(42);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $request->get('name', '')->willReturn('Too long');
        $request->get('callbackUrl', '')->willReturn('Not url');
        $request->get('activateUrl', '')->willReturn('Not url');

        $nameViolation = new ConstraintViolation('Too long', '', [], '', 'name', 'it is too long');
        $callbackUrlViolation = new ConstraintViolation('Not url', '', [], '', 'callbackUrl', 'it is not a url');
        $activateUrlViolation = new ConstraintViolation('Not url', '', [], '', 'activateUrl', 'it is not a url');
        $constraintList = new ConstraintViolationList([$nameViolation, $callbackUrlViolation, $activateUrlViolation]);
        $validator->validate(Argument::type(CreateTestAppCommand::class))->willReturn($constraintList);

        $this->__invoke($request)->shouldBeLike(new JsonResponse(
            [
                'errors' => [
                    [
                        'propertyPath' => 'name',
                        'message' => 'Too long',
                    ],
                    [
                        'propertyPath' => 'callbackUrl',
                        'message' => 'Not url',
                    ],
                    [
                        'propertyPath' => 'activateUrl',
                        'message' => 'Not url',
                    ],
                ],
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }
}
