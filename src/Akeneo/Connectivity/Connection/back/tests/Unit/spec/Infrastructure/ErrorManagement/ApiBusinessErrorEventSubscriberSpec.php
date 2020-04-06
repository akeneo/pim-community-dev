<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\ApiBusinessErrorEventSubscriber;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiBusinessErrorEventSubscriberSpec extends ObjectBehavior
{
    public function let(BusinessErrorRepository $repository): void
    {
        $this->beConstructedWith($repository);
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

    public function it_skips_if_the_event_does_not_come_from_product_api_route(
        $repository,
        ResponseEvent $responseEvent,
        Request $request
    ) {
        $responseEvent->getRequest()->willReturn($request);
        $request->get('_route')->willReturn('pim_not_api_nor_api_product');
        $repository->bulkInsert()->shouldNotBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }

    public function it_collects_business_errors_from_the_response_of_the_api_product(
        $repository,
        ResponseEvent $responseEvent,
        Request $request,
        Response $response
    ) {
        $responseEvent->getRequest()->willReturn($request);
        $responseEvent->getResponse()->willReturn($response);
        $response->getStatusCode()->willReturn(422);
        $response->getContent()->willReturn('{"message": "Property \"description\" does not exist. Check the expected format on the API documentation."}');
        $request->get('_route')->willReturn('pim_api_product_partial_update');
        $repository->bulkInsert(Argument::that(function (array $arg) {
            return $arg[0] instanceof BusinessError;
        }))->shouldBeCalled();

        $this->collectApiBusinessErrors($responseEvent);
    }
}
