<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllConnectedAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAllConnectedAppsActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        FindAllConnectedAppsQueryInterface $findAllConnectedAppsQuery,
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $findAllConnectedAppsQuery,
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
