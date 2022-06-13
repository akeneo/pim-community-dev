<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API;

use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\QueryMessageBus;
use Akeneo\Pim\Enrichment\Product\API\UnknownQueryException;
use Akeneo\Test\Pim\Enrichment\Product\Helper\DummyHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class QueryMessageBusSpec extends ObjectBehavior
{
    function let(DummyHandler $handler1, DummyHandler $handler2)
    {
        $this->beConstructedWith([
            'Other' => $handler1,
            GetProductUuidsQuery::class => $handler2,
        ]);
    }

    function it_is_a_query_message_bus()
    {
        $this->shouldHaveType(QueryMessageBus::class);
        $this->shouldImplement(MessageBusInterface::class);
    }

    function it_executes_the_correct_handler(DummyHandler $handler1, DummyHandler $handler2)
    {
        $query = new GetProductUuidsQuery([], 1);
        $handler1->__invoke(Argument::any())->shouldNotBeCalled();
        $result = new \stdClass();
        $handler2->__invoke($query)->shouldBeCalledOnce()->willReturn($result);

        $envelope = $this->dispatch($query);
        $envelope->shouldHaveType(Envelope::class);
        $handledStamp = $envelope->last(HandledStamp::class);
        $handledStamp->shouldHaveType(HandledStamp::class);
        $handledStamp->getResult()->shouldBe($result);
    }

    function it_throws_an_exception_when_the_query_cannot_be_handled(DummyHandler $handler1, DummyHandler $handler2)
    {
        $handler1->__invoke(Argument::any())->shouldNotBeCalled();
        $handler2->__invoke(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(UnknownQueryException::class)->during('dispatch', [new \stdClass()]);
    }
}
