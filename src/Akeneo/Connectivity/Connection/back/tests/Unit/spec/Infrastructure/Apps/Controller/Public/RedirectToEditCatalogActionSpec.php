<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBusInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\RedirectToEditCatalogAction;
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
class RedirectToEditCatalogActionSpec extends ObjectBehavior
{
    public function let(
        RouterInterface $router,
        SecurityFacade $security,
        QueryBusInterface $catalogQueryBus,
        FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
    ) {
        $this->beConstructedWith(
            $router,
            $security,
            $catalogQueryBus,
            $findOneConnectedAppByUserIdentifierQuery,
        );
    }

    public function it_is_a_redirect_to_catalog_editing_action(): void
    {
        $this->beAnInstanceOf(RedirectToEditCatalogAction::class);
    }

    public function it_throws_not_found_exception_when_catalog_id_is_wrong(QueryBusInterface $catalogQueryBus): void
    {
        $catalogQueryBus
            ->execute(new GetCatalogQuery('invalid_catalog_id'))
            ->willThrow(\Exception::class);

        $this->shouldThrow(new NotFoundHttpException())->during('__invoke', ['invalid_catalog_id']);
    }

    public function it_throws_not_found_exception_when_catalog_is_not_found(QueryBusInterface $catalogQueryBus): void
    {
        $catalogQueryBus
            ->execute(new GetCatalogQuery('invalid_catalog_id'))
            ->willReturn(null);

        $this->shouldThrow(new NotFoundHttpException())->during('__invoke', ['invalid_catalog_id']);
    }

    public function it_throws_not_found_exception_when_catalog_is_not_related_to_a_connected_app(
        QueryBusInterface $catalogQueryBus,
        FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
    ): void {
        $catalogQueryBus
            ->execute(new GetCatalogQuery('catalog_id'))
            ->willReturn(new Catalog('catalog_id', 'Catalog name', 'owner_username', false));

        $findOneConnectedAppByUserIdentifierQuery
            ->execute('owner_username')
            ->willReturn(null);

        $this->shouldThrow(new NotFoundHttpException())->during('__invoke', ['catalog_id']);
    }

    public function it_denies_user_that_cannot_manage_a_custom_app(
        QueryBusInterface $catalogQueryBus,
        FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
        SecurityFacade $security,
    ): void {
        $catalogQueryBus
            ->execute(new GetCatalogQuery('catalog_id'))
            ->willReturn(new Catalog('catalog_id', 'Catalog name', 'owner_username', false));

        $findOneConnectedAppByUserIdentifierQuery
            ->execute('owner_username')
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
                true,
            ));

        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);

        $this->shouldThrow(new AccessDeniedHttpException())->during('__invoke', ['catalog_id']);
    }

    public function it_denies_user_that_cannot_manage_an_app(
        QueryBusInterface $catalogQueryBus,
        FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
        SecurityFacade $security,
    ): void {
        $catalogQueryBus
            ->execute(new GetCatalogQuery('catalog_id'))
            ->willReturn(new Catalog('catalog_id', 'Catalog name', 'owner_username', false));

        $findOneConnectedAppByUserIdentifierQuery
            ->execute('owner_username')
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

        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);

        $this->shouldThrow(new AccessDeniedHttpException())->during('__invoke', ['catalog_id']);
    }

    public function it_redirects_user_to_the_edit_catalog_page(
        QueryBusInterface $catalogQueryBus,
        FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
        SecurityFacade $security,
        RouterInterface $router,
    ): void {
        $catalogQueryBus
            ->execute(new GetCatalogQuery('catalog_id'))
            ->willReturn(new Catalog('catalog_id', 'Catalog name', 'owner_username', false));

        $findOneConnectedAppByUserIdentifierQuery
            ->execute('owner_username')
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

        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(true);

        $router
            ->generate('akeneo_connectivity_connection_connect_connected_apps_catalogs_edit', [
                'connectionCode' => 'random_connection_code',
                'catalogId' => 'catalog_id',
            ])
            ->willReturn('/connect/connected-apps/connected_app/catalogs/catalog_id');

        $this
            ->__invoke('catalog_id')
            ->shouldBeLike(new RedirectResponse('/#/connect/connected-apps/connected_app/catalogs/catalog_id'));
    }
}
