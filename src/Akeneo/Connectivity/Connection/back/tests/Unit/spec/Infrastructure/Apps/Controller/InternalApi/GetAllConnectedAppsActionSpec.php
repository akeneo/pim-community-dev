<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAllConnectedAppsActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        ConnectedAppRepositoryInterface $connectedAppRepository,
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $connectedAppRepository,
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
}
