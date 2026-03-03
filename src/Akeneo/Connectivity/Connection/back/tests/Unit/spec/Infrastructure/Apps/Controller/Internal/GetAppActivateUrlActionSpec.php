<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAppActivateUrlActionSpec extends ObjectBehavior
{
    public function let(
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
        FeatureFlag $marketplaceActivateFeatureFlag,
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
    ): void {
        $this->beConstructedWith(
            $getAppQuery,
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
            $security,
            $marketplaceActivateFeatureFlag,
            $isConnectionsNumberLimitReachedQuery,
        );
    }

    public function it_redirects_on_missing_xmlhttprequest_header(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
    ): void {
        $this->__invoke($request, 'foo')
            ->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_throws_not_found_exception_with_feature_flag_disabled(
        FeatureFlag $marketplaceActivateFeatureFlag,
        Request $request,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_bad_request_exception_when_too_much_apps(
        FeatureFlag $marketplaceActivateFeatureFlag,
        SecurityFacade $security,
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
        Request $request,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $isConnectionsNumberLimitReachedQuery->execute()->willReturn(true);

        $this
            ->shouldThrow(new BadRequestHttpException('App and connections limit reached'))
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_not_found_exception_with_wrong_app_identifier(
        FeatureFlag $marketplaceActivateFeatureFlag,
        SecurityFacade $security,
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
        GetAppQueryInterface $getAppQuery,
        Request $request,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $isConnectionsNumberLimitReachedQuery->execute()->willReturn(false);
        $getAppQuery->execute('foo')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Invalid app identifier'))
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_access_denied_exception_when_the_app_is_found_but_manage_apps_permission_is_missing(
        FeatureFlag $marketplaceActivateFeatureFlag,
        SecurityFacade $security,
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
        GetAppQueryInterface $getAppQuery,
        Request $request,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $isConnectionsNumberLimitReachedQuery->execute()->willReturn(false);

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

    public function it_throws_access_denied_exception_when_the_custom_app_is_found_but_manage_apps_permission_is_missing(
        FeatureFlag $marketplaceActivateFeatureFlag,
        SecurityFacade $security,
        IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery,
        GetAppQueryInterface $getAppQuery,
        Request $request,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $isConnectionsNumberLimitReachedQuery->execute()->willReturn(false);

        $clientId = 'a_client_id';
        $app = App::fromCustomAppValues([
            'id' => $clientId,
            'name' => 'custom app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
        ]);
        $getAppQuery->execute($clientId)->willReturn($app);

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(AccessDeniedHttpException::class)
            ->during('__invoke', [$request, $clientId]);
    }
}
