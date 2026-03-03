<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAllConnectedAppScopeMessagesActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $security,
            $findOneConnectedAppByConnectionCodeQuery,
            new ScopeMapperRegistry([]),
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

    public function it_redirects_on_missing_xmlhttprequest_header(
        FeatureFlag $featureFlag,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);

        $this->__invoke($request, 'foo')
            ->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_throws_not_found_exception_with_wrong_connection_code(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn(null);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);

        $this
            ->shouldThrow(new NotFoundHttpException('Connected app with connection code foo does not exist.'))
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_access_denied_exception_with_missing_manage_apps_acl(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn($connectedApp);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request, 'foo']);
    }
}
