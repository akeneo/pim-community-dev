<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\DeleteCustomAppAction;
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
class DeleteCustomAppActionSpec extends ObjectBehavior
{
    public function let(
        SecurityFacade $security,
        DeleteCustomAppHandler $deleteCustomAppHandler,
        GetCustomAppQueryInterface $getCustomAppQuery,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $this->beConstructedWith(
            $security,
            $deleteCustomAppHandler,
            $getCustomAppQuery,
            $deleteAppHandler,
        );
    }

    public function it_is_a_delete_custom_app_action(): void
    {
        $this->shouldHaveType(DeleteCustomAppAction::class);
    }

    public function it_throws_an_access_denied_exception_when_connection_cannot_manage_custom_apps(
        SecurityFacade $security,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', ['test_client_id']);
    }

    public function it_throws_a_not_found_exception_when_client_id_do_not_belong_to_a_custom_app(
        SecurityFacade $security,
        GetCustomAppQueryInterface $getCustomAppQuery,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $getCustomAppQuery->execute('test_client_id')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Test app with test_client_id client_id was not found.'))
            ->during('__invoke', ['test_client_id']);
    }

    public function it_deletes_custom_app(
        SecurityFacade $security,
        GetCustomAppQueryInterface $getCustomAppQuery,
        DeleteCustomAppHandler $deleteCustomAppHandler,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $getCustomAppQuery->execute('test_client_id')->willReturn([
            'some' => 'data',
            'connected' => false,
        ]);

        $deleteCustomAppHandler
            ->handle(Argument::type(DeleteCustomAppCommand::class))
            ->shouldBeCalledOnce();

        $deleteAppHandler
            ->handle(Argument::type(DeleteAppCommand::class))
            ->shouldNotBeCalled();

        $this
            ->__invoke('test_client_id')
            ->shouldBeLike(new JsonResponse(null, Response::HTTP_NO_CONTENT));
    }

    public function it_deletes_custom_app_and_underlying_connected_app(
        SecurityFacade $security,
        GetCustomAppQueryInterface $getCustomAppQuery,
        DeleteCustomAppHandler $deleteCustomAppHandler,
        DeleteAppHandler $deleteAppHandler,
    ): void {
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);

        $getCustomAppQuery->execute('test_client_id')->willReturn([
            'some' => 'data',
            'connected' => true,
        ]);

        $deleteCustomAppHandler
            ->handle(Argument::type(DeleteCustomAppCommand::class))
            ->shouldBeCalledOnce();

        $deleteAppHandler
            ->handle(Argument::type(DeleteAppCommand::class))
            ->shouldBeCalledOnce();

        $this
            ->__invoke('test_client_id')
            ->shouldBeLike(new JsonResponse(null, Response::HTTP_NO_CONTENT));
    }
}
