<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetOpenAppUrlActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $marketplaceActivateFeatureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        GetAppQueryInterface $getAppQuery,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        Request $request
    ): void {
        $app = App::fromWebMarketplaceValues([
            'id' => 'connected_app_id',
            'name' => 'connected_app_name',
            'logo' => 'a/path/to/a/logo',
            'author' => 'author',
            'partner' => 'partner',
            'description' => 'a_description',
            'url' => 'https://marketplace.akeneo.com/app/connected_app_name',
            'categories' => [],
            'certified' => false,
            'activate_url' => 'http://app.example.com/activate',
            'callback_url' => 'http://app.example.com/callback',
        ]);

        $connectedApp = new ConnectedApp(
            'connected_app_id',
            'connected_app_name',
            ['some_scope'],
            'connection_code',
            'a/path/to/a/logo',
            'author',
            'group',
            'an_username',
            [],
            false,
            null,
            false,
            false
        );

        $request->isXmlHttpRequest()->willReturn(true);
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $findOneConnectedAppByConnectionCodeQuery->execute('connection_code')->willReturn($connectedApp);
        $getAppQuery->execute('connected_app_id')->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);

        $this->beConstructedWith(
            $marketplaceActivateFeatureFlag,
            $security,
            $findOneConnectedAppByConnectionCodeQuery,
            $getAppQuery,
            $saveConnectedAppOutdatedScopesFlagQuery,
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
        );
    }

    public function it_redirects_on_missing_xmlhttprequest_header(
        Request $request
    ): void {
        $request->isXmlHttpRequest()->willReturn(false);

        $this
            ->__invoke($request, 'connection_code')
            ->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_throws_a_not_found_exception_when_feature_flag_is_disabled(
        Request $request,
        FeatureFlag $marketplaceActivateFeatureFlag,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(NotFoundHttpException::class)
            ->during('__invoke', [$request, 'connection_code']);
    }

    public function it_throws_a_not_found_exception_when_a_non_connection_code_is_provided(
        Request $request,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
    ): void {
        $findOneConnectedAppByConnectionCodeQuery->execute('non_app_connection_code')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Connected app with connection code non_app_connection_code does not exist.'))
            ->during('__invoke', [$request, 'non_app_connection_code']);
    }

    public function it_expects_the_connected_app_to_have_its_app_store_counterpart(
        Request $request,
        GetAppQueryInterface $getAppQuery,
    ): void {
        $getAppQuery->execute('connected_app_id')->willReturn(null);

        $this
            ->shouldThrow(new \LogicException('App not found with connected app id "connected_app_id"'))
            ->during('__invoke', [$request, 'connection_code']);
    }

    public function it_denies_access_to_users_who_cannot_manage_or_open_apps(
        Request $request,
        SecurityFacade $security,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(AccessDeniedHttpException::class)
            ->during('__invoke', [$request, 'connection_code']);
    }

    public function it_denies_access_to_users_who_cannot_manage_custom_apps(
        Request $request,
        SecurityFacade $security,
        GetAppQueryInterface $getAppQuery,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $getAppQuery->execute('connected_app_id')->willReturn(App::fromCustomAppValues([
            'id' => 'connected_app_id',
            'name' => 'custom app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
        ]));

        $this
            ->shouldThrow(AccessDeniedHttpException::class)
            ->during('__invoke', [$request, 'connection_code']);
    }

    public function it_clears_connected_app_from_outdated_scope_flag(
        Request $request,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
    ): void {
        $findOneConnectedAppByConnectionCodeQuery
            ->execute('connection_code')
            ->willReturn(new ConnectedApp(
                'connected_app_id',
                'connected_app_name',
                ['some_scope'],
                'connection_code',
                'a/path/to/a/logo',
                'author',
                'group',
                'an_username',
                [],
                false,
                null,
                false,
                false,
                true
            ));

        $this->__invoke($request, 'connection_code');

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('connected_app_id', false)
            ->shouldHaveBeenCalled();
    }

    public function it_does_not_update_the_flag_if_connected_app_is_not_flagged(
        Request $request,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
    ): void {
        $this->__invoke($request, 'connection_code');

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('connected_app_id', false)
            ->shouldNotBeCalled();
    }

    public function it_does_not_update_the_flag_if_user_cannot_manage_apps(
        Request $request,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $findOneConnectedAppByConnectionCodeQuery
            ->execute('connection_code')
            ->willReturn(new ConnectedApp(
                'connected_app_id',
                'connected_app_name',
                ['some_scope'],
                'connection_code',
                'a/path/to/a/logo',
                'author',
                'group',
                'an_username',
                [],
                false,
                null,
                false,
                false,
                true
            ));

        $this->__invoke($request, 'connection_code');

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('connected_app_id', false)
            ->shouldNotBeCalled();
    }

    public function it_returns_url_to_open_the_app_with_pim_url_within(
        Request $request,
        SecurityFacade $security
    ): void {
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this->__invoke($request, 'connection_code')->shouldBeLike(new JsonResponse([
            'url' => 'http://app.example.com/activate?pim_url=https%3A%2F%2Fsome_pim_url'
        ]));
    }
}
