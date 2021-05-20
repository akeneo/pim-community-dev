<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\ReadProductsEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReadProductsEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        ConnectionContext $connectionContext,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler,
        ConnectionRepository $connectionRepository
    ): void {
        $this->beConstructedWith(
            $connectionContext,
            $updateDataDestinationProductEventCountHandler,
            $connectionRepository
        );
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ReadProductsEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([ReadProductsEvent::class => 'saveReadProducts',]);
    }

    public function it_saves_read_products_events_with_a_rest_api_event(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection,
        ConnectionCode $connectionCode
    ): void {
        $readProductsEvent = new ReadProductsEvent(3);
        $connection->hasDataDestinationFlowType()->willReturn(true)->shouldBeCalledTimes(1);
        $connection->auditable()->willReturn(true)->shouldBeCalledTimes(1);
        $connection->code()->willReturn($connectionCode)->shouldBeCalledTimes(1);
        $connectionCode->__toString()->willReturn('ecommerce')->shouldBeCalledTimes(1);
        $connectionContext->getConnection()->willReturn($connection)->shouldBeCalledTimes(2);
        $connectionContext->isCollectable()->shouldNotBeCalled();
        $connectionContext->areCredentialsValidCombination()->willReturn(true)->shouldBeCalledTimes(1);

        $this->saveReadProducts($readProductsEvent);

        $updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                'ecommerce',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                3
            )
        )->shouldBeCalled();
    }

    public function it_saves_read_products_events_with_an_event_api_event(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection,
        ConnectionCode $connectionCode,
        ConnectionRepository $connectionRepository
    ): void {
        $readProductsEvent = new ReadProductsEvent(3, 'ecommerce', true);
        $connection->hasDataDestinationFlowType()->willReturn(true)->shouldBeCalledTimes(1);
        $connection->auditable()->willReturn(true)->shouldBeCalledTimes(1);
        $connection->code()->shouldNotBeCalled();
        $connectionContext->getConnection()->willReturn($connection)->shouldBeCalledTimes(1);
        $connectionContext->isCollectable()->shouldNotBeCalled();
        $connectionContext->areCredentialsValidCombination()->shouldNotBeCalled();

        $connectionRepository->findOneByCode('ecommerce')
            ->willReturn($connection)->shouldBeCalledTimes(1);
        $this->saveReadProducts($readProductsEvent);

        $updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                'ecommerce',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                3
            )
        )->shouldBeCalled();
    }

    public function it_does_not_save_read_products_events_for_not_auditable_connection(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection,
        ConnectionCode $connectionCode
    ): void {
        $connection->auditable()->willReturn(false)->shouldBeCalledTimes(1);
        $connection->code()->willReturn($connectionCode)->shouldBeCalledTimes(1);
        $connection->hasDataDestinationFlowType()->shouldNotBeCalled();
        $connectionCode->__toString()->willReturn('ecommerce')->shouldBeCalledTimes(1);
        $connectionContext->areCredentialsValidCombination()->shouldNotBeCalled();
        $connectionContext->isCollectable()->shouldNotBeCalled();
        $connectionContext->getConnection()->willReturn($connection)->shouldBeCalledTimes(2);
        $updateDataDestinationProductEventCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->during('saveReadProducts', [new ReadProductsEvent(3)]);
    }

    public function it_does_not_save_read_products_events_when_the_connection_flow_type_is_different_than_destination(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection,
        ConnectionCode $connectionCode
    ): void {
        $connection->auditable()->willReturn(true)->shouldBeCalledTimes(1);
        $connection->code()->willReturn($connectionCode)->shouldBeCalledTimes(1);
        $connection->hasDataDestinationFlowType()->willReturn(false)->shouldBeCalledTimes(1);
        $connectionCode->__toString()->willReturn('ecommerce')->shouldBeCalledTimes(1);
        $connectionContext->areCredentialsValidCombination()->shouldNotBeCalled();
        $connectionContext->isCollectable()->shouldNotBeCalled();
        $connectionContext->getConnection()->willReturn($connection)->shouldBeCalledTimes(2);
        $updateDataDestinationProductEventCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->during('saveReadProducts', [new ReadProductsEvent(3)]);
    }

    public function it_does_not_save_read_products_events_if_no_valid_credentials(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection,
        ConnectionCode $connectionCode
    ): void {
        $connection->auditable()->willReturn(true)->shouldBeCalledTimes(1);
        $connection->code()->willReturn($connectionCode)->shouldBeCalledTimes(1);
        $connection->hasDataDestinationFlowType()->willReturn(true)->shouldBeCalledTimes(1);
        $connectionCode->__toString()->willReturn('ecommerce')->shouldBeCalledTimes(1);
        $connectionContext->areCredentialsValidCombination()->willReturn(false)->shouldBeCalledTimes(1);
        $connectionContext->isCollectable()->shouldNotBeCalled();
        $connectionContext->getConnection()->willReturn($connection)->shouldBeCalledTimes(2);
        $updateDataDestinationProductEventCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->during('saveReadProducts', [new ReadProductsEvent(3)]);
    }
}
