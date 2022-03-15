<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\AuthorizeAction;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizeActionSpec extends ObjectBehavior
{
    public function let(
        RequestAppAuthorizationHandler $requestAppAuthorizationHandler,
        RouterInterface $router,
        FeatureFlag $marketplaceActivateFeatureFlag,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        RedirectUriWithAuthorizationCodeGeneratorInterface $redirectUriWithAuthorizationCodeGenerator,
        ConnectedPimUserProvider $connectedPimUserProvider,
        RequestAppAuthenticationHandler $requestAppAuthenticationHandler,
        SecurityFacade $security,
        GetAppQueryInterface $getAppQuery,
    ): void {
        $this->beConstructedWith(
            $requestAppAuthorizationHandler,
            $router,
            $marketplaceActivateFeatureFlag,
            $appAuthorizationSession,
            $getAppConfirmationQuery,
            $redirectUriWithAuthorizationCodeGenerator,
            $connectedPimUserProvider,
            $requestAppAuthenticationHandler,
            $security,
            $getAppQuery,
        );
    }

    public function it_is_an_authorize_action(): void
    {
        $this->beAnInstanceOf(AuthorizeAction::class);
    }

    public function it_throws_not_found_exception_with_feature_flag_disabled(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request]);
    }

    public function it_redirects_because_there_is_no_client_id(
        FeatureFlag $marketplaceActivateFeatureFlag,
        RouterInterface $router,
        Request $request,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->query = new InputBag();
        $router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
            'error' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
        ])->willReturn('/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found');

        $this->__invoke($request)
            ->shouldBeLike(new RedirectResponse('/#/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found'));
    }

    public function it_redirects_because_there_is_no_app_matching_the_client_id(
        FeatureFlag $marketplaceActivateFeatureFlag,
        RouterInterface $router,
        Request $request,
        GetAppQueryInterface $getAppQuery,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $clientId = 'invalid_client_id';
        $request->query = new InputBag(['client_id' => $clientId]);
        $router->generate('akeneo_connectivity_connection_connect_apps_authorize', [
            'error' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
        ])->willReturn('/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found');

        $getAppQuery->execute($clientId)->willReturn(null);

        $this->__invoke($request)
            ->shouldBeLike(new RedirectResponse('/#/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found'));
    }

    public function it_throws_access_denied_exception_when_the_app_is_found_but_permissions_are_missing(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $clientId = 'a_client_id';
        $request->query = new InputBag(['client_id' => $clientId]);

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

        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(AccessDeniedHttpException::class)
            ->during('__invoke', [$request]);
    }

    public function it_throws_access_denied_exception_when_the_test_app_is_found_but_permissions_are_missing(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $clientId = 'a_client_id';
        $request->query = new InputBag(['client_id' => $clientId]);

        $app = App::fromTestAppValues([
            'id' => $clientId,
            'name' => 'test app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(AccessDeniedHttpException::class)
            ->during('__invoke', [$request, $clientId]);
    }
}
