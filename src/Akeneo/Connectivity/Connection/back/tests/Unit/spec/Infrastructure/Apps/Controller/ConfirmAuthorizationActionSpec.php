<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\ConfirmAuthorizationAction;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationActionSpec extends ObjectBehavior
{
    public function let(
        CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        LoggerInterface $logger,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler
    ): void {
        $this->beConstructedWith(
            $createAppWithAuthorizationHandler,
            $featureFlag,
            $getAppConfirmationQuery,
            $violationListNormalizer,
            $logger,
            $redirectUriWithAuthorizationCodeGenerator,
            $appAuthorizationSession,
            $connectedPimUserProvider,
            $consentAppAuthenticationHandler
        );
    }

    public function it_is_a_confirmation_authorization_action(): void
    {
        $this->beAnInstanceOf(ConfirmAuthorizationAction::class);
    }

    public function it_throws_invalid_app_authorization_request_because_create_app_validation_failed(
        CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        LoggerInterface $logger,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request
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

        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $createAppWithAuthorizationHandler->handle(
            new CreateAppWithAuthorizationCommand($clientId)
        )->willThrow(new InvalidAppAuthorizationRequestException($constraintViolationList));

        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn($appAuthorization);
        $getAppConfirmationQuery->execute($clientId)->willReturn($appConfirmation);
        $logger->warning(Argument::any())->shouldBeCalledOnce();
        $violationListNormalizer->normalize(Argument::any())->willReturn($normalizedConstraintViolationList);

        $result = $this->__invoke($request, $clientId);

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode()->getWrappedObject());
        Assert::assertEquals(
            json_encode([
                'errors' => $normalizedConstraintViolationList,
            ]),
            $result->getContent()->getWrappedObject()
        );
    }

    public function it_throws_invalid_app_authentication_exception_because_consent_app_validation_failed(
        CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        ViolationListNormalizer $violationListNormalizer,
        LoggerInterface $logger,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler
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

        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $createAppWithAuthorizationHandler->handle(
            new CreateAppWithAuthorizationCommand($clientId)
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
            json_encode([
                'errors' => $normalizedConstraintViolationList,
            ]),
            $result->getContent()->getWrappedObject()
        );
    }

    public function it_throws_a_logic_exception_because_there_is_no_active_app_authorization_in_session(
        CreateAppWithAuthorizationHandler $createAppWithAuthorizationHandler,
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler
    ): void {
        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
        ]);

        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);
        $createAppWithAuthorizationHandler->handle(
            new CreateAppWithAuthorizationCommand($clientId)
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
}
