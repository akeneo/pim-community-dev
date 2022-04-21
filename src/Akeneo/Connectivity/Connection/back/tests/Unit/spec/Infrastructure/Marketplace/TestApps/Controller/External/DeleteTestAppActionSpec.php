<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External\DeleteTestAppAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
        DeleteTestAppHandler $deleteTestAppHandler,
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $this->beConstructedWith(
            $developerModeFeatureFlag,
            $security,
            $deleteTestAppHandler,
            $getTestAppQuery,
            $deleteAppHandler,
        );
    }

    public function it_is_a_delete_test_app_action(): void
    {
        $this->shouldHaveType(DeleteTestAppAction::class);
    }

    public function it_throws_a_not_found_exception_when_developer_mode_feature_flag_is_disabled(
        FeatureFlag $developerModeFeatureFlag,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(false);

        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', ['test_client_id']);
    }

    public function it_throws_a_access_denied_exception_when_connection_cannot_manage_test_apps(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', ['test_client_id']);
    }

    public function it_throws_a_not_found_exception_when_client_id_do_not_belong_to_a_test_app(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
        GetTestAppQueryInterface $getTestAppQuery,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $getTestAppQuery->execute('test_client_id')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Test app with test_client_id client_id was not found.'))
            ->during('__invoke', ['test_client_id']);
    }

    public function it_deletes_test_app(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteTestAppHandler $deleteTestAppHandler,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $getTestAppQuery->execute('test_client_id')->willReturn([
            'some' => 'data',
        ]);

        $deleteTestAppHandler
            ->handle(Argument::type(DeleteTestAppCommand::class))
            ->shouldBeCalledOnce();

        $deleteAppHandler
            ->handle(Argument::type(DeleteAppCommand::class))
            ->shouldNotBeCalled();

        $this
            ->__invoke('test_client_id')
            ->shouldBeLike(new JsonResponse(null, Response::HTTP_NO_CONTENT));
    }

    public function it_deletes_test_app_and_underlying_connected_app(
        FeatureFlag $developerModeFeatureFlag,
        SecurityFacade $security,
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteTestAppHandler $deleteTestAppHandler,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $developerModeFeatureFlag->isEnabled()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $getTestAppQuery->execute('test_client_id')->willReturn([
            'some' => 'data',
            'connected' => true,
        ]);

        $deleteTestAppHandler
            ->handle(Argument::type(DeleteTestAppCommand::class))
            ->shouldBeCalledOnce();

        $deleteAppHandler
            ->handle(Argument::type(DeleteAppCommand::class))
            ->shouldBeCalledOnce();

        $this
            ->__invoke('test_client_id')
            ->shouldBeLike(new JsonResponse(null, Response::HTTP_NO_CONTENT));
    }
}
