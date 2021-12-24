<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\InternalApi;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
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
    ): void {
        $this->beConstructedWith(
            $featureFlag,
            $security,
            $findAConnectionHandler,
            $updateConnectionHandler,
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

    public function it_throws_access_denied_exception_with_missing_acl(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedHttpException())
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_not_found_exception_with_wrong_connection_code(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindAConnectionHandler $findAConnectionHandler,
        Request $request,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findAConnectionHandler->handle(new FindAConnectionQuery('foo'))->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException("Connection with connection code foo does not exist."))
            ->during('__invoke', [$request, 'foo']);
    }

    public function it_throws_unprocessed_entity_on_update_with_unknown_flow_type_value(
        FeatureFlag $featureFlag,
        SecurityFacade $security,
        FindAConnectionHandler $findAConnectionHandler,
        Request $request,
        ConnectionWithCredentials $connection,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findAConnectionHandler->handle(new FindAConnectionQuery('foo'))->willReturn($connection);
        $connection->type()->willReturn(ConnectionType::APP_TYPE);
        $request->getContent()->willReturn(
            json_encode([
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
        FindAConnectionHandler $findAConnectionHandler,
        Request $request,
        ConnectionWithCredentials $connection,
    ): void {
        $featureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $findAConnectionHandler->handle(new FindAConnectionQuery('foo'))->willReturn($connection);
        $connection->type()->willReturn(ConnectionType::APP_TYPE);
        $request->getContent()->willReturn(
            json_encode([
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
