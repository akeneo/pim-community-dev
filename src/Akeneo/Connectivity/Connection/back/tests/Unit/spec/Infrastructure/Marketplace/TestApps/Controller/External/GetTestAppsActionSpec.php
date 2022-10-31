<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External\GetTestAppsAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Api\Pagination\OffsetHalPaginator;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTestAppsActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
        GetTestAppsQueryInterface $getTestAppsQuery,
        OffsetHalPaginator $offsetPaginator,
    ) {
        $this->beConstructedWith(
            $developerModeFeatureFlag,
            $security,
            $tokenStorage,
            $getTestAppsQuery,
            $offsetPaginator,
        );
    }

    public function it_is_a_get_test_apps_action(): void
    {
        $this->shouldHaveType(GetTestAppsAction::class);
    }

    public function it_throws_a_not_found_exception_when_developer_mode_feature_flag_is_disabled(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException('Developer mode disabled'))
            ->during('__invoke', [$request]);
    }

    public function it_throws_an_access_denied_exception_when_connection_cannot_manage_test_apps(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException('Access forbidden. You are not allowed to manage test apps.'))
            ->during('__invoke', [$request]);
    }

    public function it_throws_a_bad_request_exception_when_token_storage_have_no_token(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        SecurityFacade $security,
        TokenStorageInterface $tokenStorage,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $tokenStorage->getToken()->willReturn(null);

        $this
            ->shouldThrow(new BadRequestHttpException('Invalid user token.'))
            ->during('__invoke', [$request]);
    }
}
