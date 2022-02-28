<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\ConfirmAuthenticationAction;
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
class ConfirmAuthenticationActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        SecurityFacade $security,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        ConnectedPimUserProvider $connectedPimUserProvider,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        LoggerInterface $logger,
        ViolationListNormalizer $violationListNormalizer
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $getAppConfirmationQuery,
            $security,
            $redirectUriWithAuthorizationCodeGenerator,
            $appAuthorizationSession,
            $connectedPimUserProvider,
            $consentAppAuthenticationHandler,
            $logger,
            $violationListNormalizer
        );
    }

    public function it_is_confirm_authentication_action(): void
    {
        $this->beAnInstanceOf(ConfirmAuthenticationAction::class);
    }

    public function it_throws_not_found_exception_with_feature_flag_disabled(
        FeatureFlag $featureFlag,
        Request $request
    ) {
        $clientId = 'a_client_id';

        $featureFlag->isEnabled()->willReturn(false);

        $this->shouldThrow(new NotFoundHttpException())->during('__invoke', [$request, $clientId]);
    }

    public function it_throws_access_denied_exception_with_missing_acl(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        Request $request
    ): void {
        $clientId = 'a_client_id';

        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(false);

        $this->shouldThrow(new AccessDeniedHttpException())->during('__invoke', [$request, $clientId]);
    }

    public function it_redirects_if_not_xml_http_request(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        Request $request
    ): void {
        $clientId = 'a_client_id';

        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(false);

        $this->__invoke($request, $clientId)->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_failed_because_of_consent_app_authentication_validation_error(
        FeatureFlag $featureFlag,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        ViolationListNormalizer $violationListNormalizer,
        SecurityFacade $security,
        ConnectedPimUserProvider $connectedPimUserProvider,
        LoggerInterface $logger,
        Request $request
    ): void {
        $clientId = 'a_client_id';
        $connectedPimUserId = 1;
        $constraintViolationList = new ConstraintViolationList([
            new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
        ]);
        $normalizedConstraintViolationList = [
            [
                'message' => 'a_violated_constraint_message',
                'property_path' => 'a_property_path',
            ],
        ];

        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);

        $consentAppAuthenticationHandler->handle(
            new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId)
        )->willThrow(new InvalidAppAuthenticationException($constraintViolationList));

        $logger->warning(
            'App activation failed with validation error "a_violated_constraint_message"'
        )->shouldBeCalledOnce();

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

    public function it_failed_because_of_consent_app_authentication_logic_exception(
        FeatureFlag $featureFlag,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        SecurityFacade $security,
        ConnectedPimUserProvider $connectedPimUserProvider,
        Request $request
    ): void {
        $clientId = 'a_client_id';
        $connectedPimUserId = 1;

        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);
        $consentAppAuthenticationHandler->handle(
            new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId)
        )->willThrow(new \LogicException('a_logic_exception_message'));

        $this->shouldThrow(new \LogicException('a_logic_exception_message'))->during(
            '__invoke',
            [$request, $clientId]
        );
    }

    public function it_throws_a_logic_exception_because_there_is_no_app_authorization_in_session(
        FeatureFlag $featureFlag,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        SecurityFacade $security,
        ConnectedPimUserProvider $connectedPimUserProvider,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        Request $request
    ): void {
        $clientId = 'a_client_id';
        $connectedPimUserId = 1;

        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);
        $consentAppAuthenticationHandler->handle(
            new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId)
        )->shouldBeCalledOnce();

        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn(null);

        $this->shouldThrow(new \LogicException('There is no active app authorization in session'))->during(
            '__invoke',
            [$request, $clientId]
        );
    }

    public function it_throws_a_logic_exception_because_there_is_no_connected_app(
        FeatureFlag $featureFlag,
        ConsentAppAuthenticationHandler $consentAppAuthenticationHandler,
        SecurityFacade $security,
        ConnectedPimUserProvider $connectedPimUserProvider,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        Request $request
    ): void {
        $clientId = 'a_client_id';
        $connectedPimUserId = 1;

        $featureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedPimUserProvider->getCurrentUserId()->willReturn($connectedPimUserId);
        $consentAppAuthenticationHandler->handle(
            new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId)
        )->shouldBeCalledOnce();

        $appAuthorizationSession->getAppAuthorization($clientId)->willReturn(AppAuthorization::createFromNormalized([
            'client_id' => $clientId,
            'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
            'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a_state',
        ]));

        $getAppConfirmationQuery->execute($clientId)->willReturn(null);

        $this->shouldThrow(new \LogicException('The connected app should have been created'))->during(
            '__invoke',
            [$request, $clientId]
        );
    }
}
