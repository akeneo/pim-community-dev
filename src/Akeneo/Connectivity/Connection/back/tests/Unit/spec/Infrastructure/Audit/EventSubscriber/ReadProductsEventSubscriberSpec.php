<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Audit\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\EventSubscriber\ReadProductsEventSubscriber;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
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
        ConnectionRepositoryInterface $connectionRepository
    ): void {
        $this->beConstructedWith(
            $connectionContext,
            $updateDataDestinationProductEventCountHandler,
            $connectionRepository
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ReadProductsEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([ReadProductsEvent::class => 'saveReadProducts',]);
    }

    public function it_saves_read_products_events_without_connection_code_from_the_rest_api(
        ConnectionContext $connectionContext,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler,
        Connection $connection
    ): void {
        $readProductsEvent = new ReadProductsEvent(3);

        $connection->auditable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_DESTINATION));
        $connection->code()->willReturn(new ConnectionCode('connection_code'));
        $connectionContext->areCredentialsValidCombination()->willReturn(true);
        $connectionContext->getConnection()->willReturn($connection);

        $updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                'connection_code',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                3
            )
        )->shouldBeCalledTimes(1);

        $this->saveReadProducts($readProductsEvent);
    }

    public function it_saves_read_products_events_with_connection_code_from_the_events_api(
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler,
        ConnectionRepositoryInterface $connectionRepository,
        Connection $connection
    ): void {
        $readProductsEvent = new ReadProductsEvent(3, 'connection_code');

        $connectionRepository->findOneByCode('connection_code')
            ->willReturn($connection);

        $connection->auditable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_DESTINATION));
        $connection->code()->willReturn(new ConnectionCode('connection_code'));

        $updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                'connection_code',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                3
            )
        )->shouldBeCalledTimes(1);

        $this->saveReadProducts($readProductsEvent);
    }

    public function it_does_not_save_read_products_events_when_the_connection_is_not_using_valid_credentials(
        ConnectionContext $connectionContext,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler
    ): void {
        $readProductsEvent = new ReadProductsEvent(3);

        $connectionContext->areCredentialsValidCombination()->willReturn(false);

        $updateDataDestinationProductEventCountHandler->handle(Argument::any())
            ->shouldNotBeCalled();

        $this->saveReadProducts($readProductsEvent);
    }

    public function it_does_not_save_read_products_events_when_the_connection_is_not_auditable(
        ConnectionContext $connectionContext,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler,
        Connection $connection
    ): void {
        $readProductsEvent = new ReadProductsEvent(3);

        $connection->auditable()->willReturn(false);
        $connectionContext->areCredentialsValidCombination()->willReturn(true);
        $connectionContext->getConnection()->willReturn($connection);

        $updateDataDestinationProductEventCountHandler->handle(Argument::any())
            ->shouldNotBeCalled();

        $this->saveReadProducts($readProductsEvent);
    }

    public function it_does_not_save_read_products_events_when_the_connection_is_not_a_destination(
        ConnectionContext $connectionContext,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler,
        Connection $connection
    ): void {
        $readProductsEvent = new ReadProductsEvent(3);

        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $connection->auditable()->willReturn(true);
        $connectionContext->areCredentialsValidCombination()->willReturn(true);
        $connectionContext->getConnection()->willReturn($connection);

        $updateDataDestinationProductEventCountHandler->handle(Argument::any())
            ->shouldNotBeCalled();

        $this->saveReadProducts($readProductsEvent);
    }
}
