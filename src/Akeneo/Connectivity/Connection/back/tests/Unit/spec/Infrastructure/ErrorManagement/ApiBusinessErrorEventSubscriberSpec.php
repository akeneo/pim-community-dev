<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\ApiBusinessErrorEventSubscriber;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiBusinessErrorEventSubscriberSpec extends ObjectBehavior
{
    public function let(BusinessErrorRepository $repository, ConnectionContext $connectionContext): void
    {
        $this->beConstructedWith($connectionContext, $repository);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::RESPONSE => 'collectApiBusinessErrors']);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(ApiBusinessErrorEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_skips_if_the_event_does_not_come_from_product_api_controller(
        $repository,
        $connectionContext,
        ResponseEvent $responseEvent,
        Request $request,
        Response $response
    ) {
        $connection = new Connection(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            1234,
            456,
            null,
            true
        );

        $connectionContext->getConnection()->willReturn($connection);
        $responseEvent->getRequest()->willReturn($request);
        $responseEvent->getResponse()->willReturn($response);
        $connectionContext->isCollectable()->willReturn(true);
        $request->get('_controller')->willReturn('pim_not_api_nor_api_product');
        $repository->bulkInsert()->shouldNotBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }

    public function it_skips_if_the_event_does_not_come_from_the_api(
        $repository,
        $connectionContext,
        ResponseEvent $responseEvent,
        Request $request,
        Response $response
    ) {
        $responseEvent->getResponse()->willReturn($response);
        $connectionContext->getConnection()->willReturn(null);
        $connectionContext->isCollectable()->willReturn(true);
        $responseEvent->getRequest()->willReturn($request);
        $request->get('_controller')->willReturn('pim_not_api');
        $repository->bulkInsert()->shouldNotBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }

    public function it_skips_if_the_request_does_not_come_from_a_source_connection(
        $repository,
        $connectionContext,
        ResponseEvent $responseEvent,
        Request $request,
        Response $response
    ) {
        $connection = new Connection(
            'erp',
            'ERP',
            FlowType::OTHER,
            1234,
            456,
            null,
            true
        );
        $responseEvent->getResponse()->willReturn($response);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $responseEvent->getRequest()->willReturn($request);
        $request->get('_controller')->willReturn('pim_api.controller.product:partialUpdateAction');
        $repository->bulkInsert()->shouldNotBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }

    public function it_skips_if_the_request_does_not_come_from_a_collectable_connection(
        $repository,
        $connectionContext,
        ResponseEvent $responseEvent,
        Request $request,
        Response $response
    ) {
        $connection = new Connection(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            1234,
            456,
            null,
            false
        );
        $responseEvent->getResponse()->willReturn($response);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(false);
        $responseEvent->getRequest()->willReturn($request);
        $request->get('_controller')->willReturn('pim_api.controller.product:partialUpdateAction');
        $repository->bulkInsert()->shouldNotBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }

    public function it_skips_if_the_response_is_a_stream(
        $repository,
        $connectionContext,
        ResponseEvent $responseEvent,
        Request $request,
        StreamedResponse $response
    ) {
        $connection = new Connection(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            1234,
            456,
            null,
            true
        );
        $responseEvent->getResponse()->willReturn($response);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $responseEvent->getRequest()->willReturn($request);
        $request->get('_controller')->willReturn('pim_api.controller.product:partialUpdateAction');
        $repository->bulkInsert()->shouldNotBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }

    public function it_collects_errors_from_the_response_of_the_api_product_and_a_collectable_source_connection(
        $repository,
        $connectionContext,
        ResponseEvent $responseEvent,
        Request $request,
        Response $response
    ) {
        $connection = new Connection(
            'erp',
            'ERP',
            FlowType::DATA_SOURCE,
            1234,
            456,
            null,
            true
        );
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $responseEvent->getRequest()->willReturn($request);
        $responseEvent->getResponse()->willReturn($response);
        $response->getStatusCode()->willReturn(422);
        $response->getContent()->willReturn('{"message": "Property \"description\" does not exist. Check the expected format on the API documentation."}');
        $request->get('_controller')->willReturn('pim_api.controller.product:partialUpdateAction');
        $repository->bulkInsert(Argument::that(function (array $arg) {
            return $arg[0] instanceof BusinessError;
        }))->shouldBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }
}
