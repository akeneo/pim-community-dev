<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetConnectedAppActionSpec extends ObjectBehavior
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

    public function it_throws_access_denied_exception_with_missing_acl(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_not_found_exception_with_wrong_connection_code(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Connected app with connection code foo does not exist.'))
            ->during('__invoke', [$request, 'foo']);
    }
}
