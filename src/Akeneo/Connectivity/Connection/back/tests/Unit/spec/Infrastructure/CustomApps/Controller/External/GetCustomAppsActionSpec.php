<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\GetCustomAppsAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppsActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $developerModeFeatureFlag,
        TokenStorageInterface $tokenStorage,
        GetCustomAppsQueryInterface $getCustomAppsQuery,
    ): void {
        $this->beConstructedWith(
            $developerModeFeatureFlag,
            $tokenStorage,
            $getCustomAppsQuery,
        );
    }

    public function it_is_a_get_custom_apps_action(): void
    {
        $this->shouldHaveType(GetCustomAppsAction::class);
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

    public function it_throws_a_bad_request_exception_when_token_storage_have_no_token(
        FeatureFlag $developerModeFeatureFlag,
        Request $request,
        TokenStorageInterface $tokenStorage,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $tokenStorage->getToken()->willReturn(null);

        $this
            ->shouldThrow(new BadRequestHttpException('Invalid user token.'))
            ->during('__invoke', [$request]);
    }
}
