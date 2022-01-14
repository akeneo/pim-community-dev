<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\DeleteTestAppHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\InternalApi\DeleteTestAppAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        FeatureFlag $developerModeFlag,
        SecurityFacade $security,
        DeleteTestAppHandler $deleteTestAppHandler,
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $this->beConstructedWith(
            $developerModeFlag,
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

    public function it_throws_not_found_exception_when_feature_flag_is_disabled(
        FeatureFlag $developerModeFlag,
        Request $request,
    ): void {
        $developerModeFlag->isEnabled()->willReturn(false);
        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', [$request, 'testAppId`']);
    }

    public function it_redirects_to_the_root_when_request_is_not_ajax(
        FeatureFlag $developerModeFlag,
        Request $request,
    ): void {
        $developerModeFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(false);

        $this->__invoke($request, 'testAppId')->shouldBeLike(new RedirectResponse('/'));
    }

    public function it_denies_access_when_acl_is_not_granted_to_the_user(
        FeatureFlag $developerModeFlag,
        Request $request,
        SecurityFacade $security,
    ): void {
        $developerModeFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request, 'testAppId']);
    }

    public function it_throws_not_found_exception_when_attempting_delete_unknown_test_app(
        FeatureFlag $developerModeFlag,
        Request $request,
        SecurityFacade $security,
        GetTestAppQueryInterface $getTestAppQuery,
    ): void {
        $developerModeFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $getTestAppQuery->execute('testAppId')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Test app with id testAppId was not found.'))
            ->during('__invoke', [$request, 'testAppId']);
    }

    public function it_only_deletes_test_app_when_its_not_connected(
        FeatureFlag $developerModeFlag,
        Request $request,
        SecurityFacade $security,
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteTestAppHandler $deleteTestAppHandler,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $developerModeFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $getTestAppQuery->execute('testAppId')->willReturn(['connected' => false]);

        $deleteTestAppHandler->handle(new DeleteTestAppCommand('testAppId'))->shouldBeCalledOnce();
        $deleteAppHandler->handle(new DeleteAppCommand('testAppId'))->shouldNotBeCalled();

        $this
            ->__invoke($request, 'testAppId')
            ->shouldBeLike(new Response(null, Response::HTTP_NO_CONTENT));
    }

    public function it_deletes_test_app_and_its_connected_app(
        FeatureFlag $developerModeFlag,
        Request $request,
        SecurityFacade $security,
        GetTestAppQueryInterface $getTestAppQuery,
        DeleteTestAppHandler $deleteTestAppHandler,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $developerModeFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $getTestAppQuery->execute('testAppId')->willReturn(['connected' => true]);

        $deleteTestAppHandler->handle(new DeleteTestAppCommand('testAppId'))->shouldBeCalledOnce();
        $deleteAppHandler->handle(new DeleteAppCommand('testAppId'))->shouldBeCalledOnce();

        $this
            ->__invoke($request, 'testAppId')
            ->shouldBeLike(new Response(null, Response::HTTP_NO_CONTENT));
    }
}
