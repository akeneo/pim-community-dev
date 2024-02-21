<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\RedirectToEditConnectedAppAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RedirectToEditConnectedAppActionSpec extends ObjectBehavior
{
    public function let(
        RouterInterface $router,
        SecurityFacade $security,
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
    ): void {
        $this->beConstructedWith(
            $router,
            $security,
            $findOneConnectedAppByIdQuery,
        );
    }

    public function it_is_a_redirect_to_connected_app_editing_action(): void
    {
        $this->beAnInstanceOf(RedirectToEditConnectedAppAction::class);
    }

    public function it_throws_not_found_exception_when_connected_app_is_not_found(
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
    ): void {
        $badId = '00000000-0000-0000-0000-000000000000';

        $findOneConnectedAppByIdQuery
            ->execute($badId)
            ->willReturn(null);

        $this->shouldThrow(new NotFoundHttpException())->during('__invoke', [$badId]);
    }

    public function it_denies_user_that_cannot_manage_a_custom_app(
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        SecurityFacade $security,
    ): void {
        $appId = '06416ae6-56e6-4a63-82af-522373fbf901';
        $findOneConnectedAppByIdQuery
            ->execute($appId)
            ->willReturn(new ConnectedApp(
                $appId,
                'a_connected_app_name',
                ['read_scope_d', 'read_scope_b'],
                'random_connection_code',
                'a/path/to/a/logo',
                'an_author',
                'a_group',
                'an_username',
                [],
                false,
                null,
                true,
            ));

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);

        $this->shouldThrow(new AccessDeniedHttpException())->during('__invoke', [$appId]);
    }

    public function it_denies_user_that_cannot_manage_an_app(
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        SecurityFacade $security,
    ): void {
        $appId = '06416ae6-56e6-4a63-82af-522373fbf901';
        $findOneConnectedAppByIdQuery
            ->execute($appId)
            ->willReturn(new ConnectedApp(
                'a_connected_app_id',
                'a_connected_app_name',
                ['read_scope_d', 'read_scope_b'],
                'random_connection_code',
                'a/path/to/a/logo',
                'an_author',
                'a_group',
                'an_username',
                [],
                false,
                null,
                false,
            ));

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);

        $this->shouldThrow(new AccessDeniedHttpException())->during('__invoke', [$appId]);
    }

    public function it_redirects_user_to_the_edit_connected_app_page(
        FindOneConnectedAppByIdQueryInterface $findOneConnectedAppByIdQuery,
        SecurityFacade $security,
        RouterInterface $router,
    ): void {
        $appId = '06416ae6-56e6-4a63-82af-522373fbf901';
        $findOneConnectedAppByIdQuery
            ->execute($appId)
            ->willReturn(new ConnectedApp(
                'a_connected_app_id',
                'a_connected_app_name',
                ['read_scope_d', 'read_scope_b'],
                'random_connection_code',
                'a/path/to/a/logo',
                'an_author',
                'a_group',
                'an_username',
                [],
                false,
                null,
                false,
            ));

        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);

        $router
            ->generate('akeneo_connectivity_connection_connect_connected_apps_edit', [
                'connectionCode' => 'random_connection_code',
            ])
            ->willReturn('/connect/connected-apps/random_connection_code');

        $this
            ->__invoke($appId)
            ->shouldBeLike(new RedirectResponse('/#/connect/connected-apps/random_connection_code'));
    }
}
