<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\CreateCustomAppAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCustomAppActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        CreateCustomAppCommandHandler $createCustomAppCommandHandler,
        GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
    ): void {
        $this->beConstructedWith(
            $developerModeFeatureFlag,
            $security,
            $validator,
            $translator,
            $tokenStorage,
            $createCustomAppCommandHandler,
            $getCustomAppSecretQuery,
        );
    }

    public function it_is_a_create_custom_app_action(): void
    {
        $this->shouldHaveType(CreateCustomAppAction::class);
    }

    public function it_throws_a_not_found_exception_when_developer_mode_feature_flag_is_disabled(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request]);
    }

    public function it_throws_an_access_denied_exception_when_connection_cannot_manage_custom_apps(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request]);
    }

    public function it_throws_a_bad_request_exception_when_token_storage_have_no_token(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $tokenStorage->getToken()->willReturn(null);

        $this
            ->shouldThrow(new BadRequestHttpException('Invalid user token.'))
            ->during('__invoke', [$request]);
    }

    public function it_throws_a_bad_request_exception_when_no_valid_user_found(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        SymfonyUserInterface $user,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);
        $this
            ->shouldThrow(new BadRequestHttpException('Invalid user token.'))
            ->during('__invoke', [$request]);
    }

    public function it_returns_a_list_of_errors_when_submit_data_is_invalid(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $validator
            ->validate(Argument::type(CreateCustomAppCommand::class))
            ->willReturn(
                new ConstraintViolationList([
                    new ConstraintViolation('Too long', '', [], '', 'name', 'it is too long'),
                    new ConstraintViolation('Not url', '', [], '', 'callbackUrl', 'it is not a url'),
                    new ConstraintViolation('Not url', '', [], '', 'activateUrl', 'it is not a url')
                ])
            );

        $translator->trans(Argument::cetera())->willReturnArgument();

        $request->get('name', '')->willReturn('Too long');
        $request->get('activate_url', '')->willReturn(420);
        $request->get('callback_url', '')->willReturn('Not url');

        $this->__invoke($request)->shouldBeLike(new JsonResponse(
            [
                'errors' => [
                    [
                        'propertyPath' => 'name',
                        'message' => 'Too long',
                    ],
                    [
                        'propertyPath' => 'callback_url',
                        'message' => 'Not url',
                    ],
                    [
                        'propertyPath' => 'activate_url',
                        'message' => 'Not url',
                    ],
                ],
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }

    public function it_fails_retrieve_the_custom_app_secret(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        ValidatorInterface $validator,
        CreateCustomAppCommandHandler $createCustomAppCommandHandler,
        GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $validator
            ->validate(Argument::type(CreateCustomAppCommand::class))
            ->willReturn(new ConstraintViolationList());

        $createCustomAppCommandHandler
            ->handle(Argument::type(CreateCustomAppCommand::class))
            ->shouldBeCalledOnce();

        $getCustomAppSecretQuery->execute(Argument::type('string'))->willReturn(null);

        $request->get('name', '')->willReturn('CustomApp');
        $request->get('activate_url', '')->willReturn('http://callback-url.test');
        $request->get('callback_url', '')->willReturn('http://activate-url.test');

        $this->__invoke($request)->shouldBeLike(new JsonResponse(
            [
                'errors' => [
                    'propertyPath' => null,
                    'message' => 'The client secret can not be retrieved.'
                ]
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }

    public function it_creates_a_custom_app(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        ValidatorInterface $validator,
        CreateCustomAppCommandHandler $createCustomAppCommandHandler,
        GetCustomAppSecretQueryInterface $getCustomAppSecretQuery,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $user->getId()->willReturn(42);
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $validator
            ->validate(Argument::type(CreateCustomAppCommand::class))
            ->willReturn(new ConstraintViolationList());

        $createCustomAppCommandHandler
            ->handle(Argument::type(CreateCustomAppCommand::class))
            ->shouldBeCalledOnce();

        $getCustomAppSecretQuery->execute(Argument::type('string'))->willReturn('app_secret');

        $request->get('name', '')->willReturn('CustomApp');
        $request->get('activate_url', '')->willReturn('http://callback-url.test');
        $request->get('callback_url', '')->willReturn('http://activate-url.test');

        $this->__invoke($request)->shouldBeAValidCreateCustomAppResponse('app_secret');
    }

    /**
     * @return array{beAValidCreateCustomAppResponse: Closure(mixed, mixed):true}
     */
    public function getMatchers(): array
    {
        return [
            'beAValidCreateCustomAppResponse' => function ($subject, $value): bool {
                if (!$subject instanceof JsonResponse || $subject->getStatusCode() !== Response::HTTP_CREATED) {
                    throw new FailureException('Response should be a JsonResponse with 201 as status code');
                }

                $jsonData = \json_decode($subject->getContent(), true, 512, JSON_THROW_ON_ERROR);
                $clientId = $jsonData['client_id'] ?? null;
                $clientSecret = $jsonData['client_secret'] ?? null;

                if ($clientId === null) {
                    throw new FailureException('client_id must exist');
                }

                if (!\is_string($clientId)) {
                    throw new FailureException('client_id is not a string');
                }

                if ($clientSecret === null) {
                    throw new FailureException('client_secret must exist');
                }

                if (!\is_string($clientSecret) || $clientSecret !== $value) {
                    throw new FailureException(\sprintf('Client secret %s do not match with expected secret (%s);', $clientSecret, $value));
                }

                return true;
            },
        ];
    }
}
