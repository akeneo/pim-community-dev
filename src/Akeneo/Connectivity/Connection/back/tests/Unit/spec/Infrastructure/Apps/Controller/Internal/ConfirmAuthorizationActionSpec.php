<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\ConfirmAuthorizationAction;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationActionSpec extends ObjectBehavior
{
    public function let(
        CreateConnectedAppWithAuthorizationHandler $createConnectedAppWithAuthorizationHandler,
        FeatureFlag $marketplaceActivateFeatureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        SecurityFacade $security,
        LoggerInterface $logger,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        GetAppQueryInterface $getAppQuery,
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        UpdateConnectedAppScopesWithAuthorizationHandler $updateConnectedAppScopesWithAuthorizationHandler,
    ): void {
        $this->beConstructedWith(
            $createConnectedAppWithAuthorizationHandler,
            $marketplaceActivateFeatureFlag,
            $getAppConfirmationQuery,
            $violationListNormalizer,
            $security,
            $logger,
            $redirectUriWithAuthorizationCodeGenerator,
            $appAuthorizationSession,
            $connectedPimUserProvider,
            $consentAppAuthenticationHandler,
            $getAppQuery,
            $findOneConnectedAppByIdQuery,
            $updateConnectedAppScopesWithAuthorizationHandler,
        );
    }

    public function it_is_a_confirmation_authorization_action(): void
    {
        $this->beAnInstanceOf(ConfirmAuthorizationAction::class);
    }

    public function it_throws_not_found_exception_with_feature_flag_disabled(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_redirects_on_missing_xmlhttprequest_header(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);

        $this->__invoke($request, 'foo')
            ->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_returns_not_found_response_because_there_is_no_app_matching_the_client_id(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
        GetAppQueryInterface $getAppQuery,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $clientId = 'a_client_id';
        $getAppQuery->execute($clientId)->willReturn(null);

        $result = $this->__invoke($request, $clientId);

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode()->getWrappedObject());
        Assert::assertEquals(
            \json_encode([
                'errors' => [
                    [
                        'message' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                    ],
                ],
            ]),
            $result->getContent()->getWrappedObject()
        );
    }

    public function it_throws_access_denied_exception_when_the_app_is_found_but_manage_apps_permission_is_missing(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $clientId = 'a_client_id';
        $app = App::fromWebMarketplaceValues([
            'id' => $clientId,
            'name' => 'some app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
            'logo' => 'logo',
            'author' => 'admin',
            'url' => 'http://manage_app.test',
            'categories' => ['master'],
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(AccessDeniedHttpException::class)
            ->during('__invoke', [$request, $clientId]);
    }

    public function it_throws_invalid_app_authorization_request_because_create_app_validation_failed(
        CreateConnectedAppWithAuthorizationHandler $createConnectedAppWithAuthorizationHandler,
        FeatureFlag $marketplaceActivateFeatureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        LoggerInterface $logger,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
        ]);
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $normalizedConstraintViolationList = [
            [
                'message' => 'a_violated_constraint_message',
                'property_path' => 'a_property_path',
            ],
        ];
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $clientId,
            'authorization_scope' => 'read_catalog_structure write_categories',
            'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a state',
        ]);

        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $app = App::fromWebMarketplaceValues([
            'id' => $clientId,
            'name' => 'some app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
            'logo' => 'logo',
            'author' => 'admin',
            'url' => 'http://manage_app.test',
            'categories' => ['master'],
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $createConnectedAppWithAuthorizationHandler->handle(
            new CreateConnectedAppWithAuthorizationCommand($clientId)
        )->willThrow(new InvalidAppAuthorizationRequestException($constraintViolationList));

        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn($appAuthorization);
        $getAppConfirmationQuery->execute($clientId)->willReturn($appConfirmation);
        $logger->warning(Argument::any())->shouldBeCalledOnce();
        $violationListNormalizer->normalize(Argument::any())->willReturn($normalizedConstraintViolationList);

        $result = $this->__invoke($request, $clientId);

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode()->getWrappedObject());
        Assert::assertEquals(
            \json_encode([
                'errors' => $normalizedConstraintViolationList,
            ]),
            $result->getContent()->getWrappedObject()
        );
    }

    public function it_throws_invalid_app_authentication_exception_because_consent_app_validation_failed(
        CreateConnectedAppWithAuthorizationHandler $createConnectedAppWithAuthorizationHandler,
        FeatureFlag $marketplaceActivateFeatureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        LoggerInterface $logger,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
        ]);
        $normalizedConstraintViolationList = [
            [
                'message' => 'a_violated_constraint_message',
                'property_path' => 'a_property_path',
            ],
        ];
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $clientId,
            'authorization_scope' => 'read_catalog_structure write_categories',
            'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a state',
        ]);

        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $app = App::fromWebMarketplaceValues([
            'id' => $clientId,
            'name' => 'some app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
            'logo' => 'logo',
            'author' => 'admin',
            'url' => 'http://manage_app.test',
            'categories' => ['master'],
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $createConnectedAppWithAuthorizationHandler->handle(
            new CreateConnectedAppWithAuthorizationCommand($clientId)
        )->shouldBeCalledOnce();
        $consentAppAuthenticationHandler->handle(
            new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId)
        )->willThrow(new InvalidAppAuthenticationException($constraintViolationList));

        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn($appAuthorization);
        $getAppConfirmationQuery->execute($clientId)->willReturn($appConfirmation);
        $logger->warning(Argument::any())->shouldBeCalledOnce();
        $violationListNormalizer->normalize(Argument::any())->willReturn($normalizedConstraintViolationList);

        $result = $this->__invoke($request, $clientId);

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode()->getWrappedObject());
        Assert::assertEquals(
            \json_encode([
                'errors' => $normalizedConstraintViolationList,
            ]),
            $result->getContent()->getWrappedObject()
        );
    }

    public function it_throws_a_logic_exception_because_there_is_no_active_app_authorization_in_session(
        CreateConnectedAppWithAuthorizationHandler $createConnectedAppWithAuthorizationHandler,
        FeatureFlag $marketplaceActivateFeatureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
        ]);

        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $app = App::fromWebMarketplaceValues([
            'id' => $clientId,
            'name' => 'some app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
            'logo' => 'logo',
            'author' => 'admin',
            'url' => 'http://manage_app.test',
            'categories' => ['master'],
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $createConnectedAppWithAuthorizationHandler->handle(
            new CreateConnectedAppWithAuthorizationCommand($clientId)
        )->shouldBeCalledOnce();
        $consentAppAuthenticationHandler->handle(
            new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId)
        )->willThrow(new InvalidAppAuthenticationException($constraintViolationList));
        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn(null);
        $getAppConfirmationQuery->execute($clientId)->willReturn($appConfirmation);

        $this->shouldThrow(new \LogicException('There is no active app authorization in session'))->during(
            '__invoke',
            [$request, $clientId]
        );
    }

    public function it_updates_when_connected_app_already_exist(
        UpdateConnectedAppScopesWithAuthorizationHandler $updateConnectedAppScopesWithAuthorizationHandler,
        FeatureFlag $marketplaceActivateFeatureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
    ): void {
        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);

        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $app = App::fromWebMarketplaceValues([
            'id' => $clientId,
            'name' => 'some app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
            'logo' => 'logo',
            'author' => 'admin',
            'url' => 'http://manage_app.test',
            'categories' => ['master'],
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $connectedApp = new ConnectedApp(
            $clientId,
            'App',
            [],
            'connectionCode_random',
            'http://www.example.com/path/to/logo',
            'author',
            'userGroup_random',
            'an_username',
            [],
            false,
            'partner'
        );

        $findOneConnectedAppByIdQuery->execute($clientId)->willReturn($connectedApp);

        $updateConnectedAppScopesWithAuthorizationHandler->handle(
            new UpdateConnectedAppScopesWithAuthorizationCommand($clientId)
        )->shouldBeCalledOnce();

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $clientId,
            'authorization_scope' => 'read_catalog_structure write_categories',
            'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a state',
        ]);

        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn($appAuthorization);
        $getAppConfirmationQuery->execute($clientId)->willReturn($appConfirmation);

        $redirectUriWithAuthorizationCodeGenerator->generate(
            $appAuthorization,
            $appConfirmation,
            $connectedPimUserId
        )->willReturn('http://url.test');

        $this->__invoke($request, $clientId);
    }
}
