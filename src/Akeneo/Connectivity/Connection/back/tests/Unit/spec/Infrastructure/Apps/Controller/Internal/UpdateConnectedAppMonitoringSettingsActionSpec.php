<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository\ConnectedAppRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateConnectedAppMonitoringSettingsActionSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindAConnectionHandler $findAConnectionHandler,
        UpdateConnectionHandler $updateConnectionHandler,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $security,
            $findAConnectionHandler,
            $updateConnectionHandler,
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

    public function it_throws_not_found_exception_with_not_existing_connected_app(
        FeatureFlag $featureFlag,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Connected app with connection code foo does not exist.'))
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_access_denied_exception_with_missing_manage_apps_acl(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn($connectedApp);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_not_found_exception_with_not_existing_connection(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        FindAConnectionHandler $findAConnectionHandler,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn($connectedApp);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findAConnectionHandler->handle(new FindAConnectionQuery('foo'))->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Connection with connection code foo does not exist.'))
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_unprocessed_entity_on_update_with_unknown_flow_type_value(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        FindAConnectionHandler $findAConnectionHandler,
        Request $request,
        ConnectionWithCredentials $connection,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn($connectedApp);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findAConnectionHandler->handle(new FindAConnectionQuery('foo'))->willReturn($connection);
        $connection->type()->willReturn(ConnectionType::APP_TYPE);
        $request->getContent()->willReturn(
            \json_encode([
                'flowType' => 0,
                'auditable' => true,
            ])
        );

        $this->__invoke($request, 'foo')
            ->shouldBeLike(
                new JsonResponse(['error' => 'Wrong type for parameters'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
    }

    public function it_throws_unprocessed_entity_on_update_with_unknown_auditable_type_value(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        FindAConnectionHandler $findAConnectionHandler,
        Request $request,
        ConnectionWithCredentials $connection,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn($connectedApp);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findAConnectionHandler->handle(new FindAConnectionQuery('foo'))->willReturn($connection);
        $connection->type()->willReturn(ConnectionType::APP_TYPE);
        $request->getContent()->willReturn(
            \json_encode([
                'flowType' => 'other',
                'auditable' => 'should be a bool',
            ])
        );

        $this->__invoke($request, 'foo')
            ->shouldBeLike(
                new JsonResponse(['error' => 'Wrong type for parameters'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
    }
}
