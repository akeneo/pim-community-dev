<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Common\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\ReadProductsEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use PhpSpec\ObjectBehavior;
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
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler
    ): void {
        $this->beConstructedWith(
            $connectionContext,
            $updateDataDestinationProductEventCountHandler
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

    public function it_saves_read_products_events(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler
    ): void {
        $connection = new Connection('ecommerce', 'ecommerce', FlowType::DATA_DESTINATION, 42, 10);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);

        $this->saveReadProducts(new ReadProductsEvent([4, 2, 6]));

        $updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                'ecommerce',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                3
            )
        )->shouldBeCalled();
    }

    public function it_does_not_save_read_products_events_for_not_collectable_connection(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler
    ): void {
        $connectionContext->isCollectable()->willReturn(false);

        $this->saveReadProducts(new ReadProductsEvent([4, 2, 6]));

        $updateDataDestinationProductEventCountHandler->handle()
            ->shouldNotBeCalled();
    }

    public function it_does_not_save_read_products_events_when_the_connection_flow_type_is_different_than_destination(
        $connectionContext,
        $updateDataDestinationProductEventCountHandler
    ): void {
        $connection = new Connection('ecommerce', 'ecommerce', FlowType::DATA_SOURCE, 42, 10);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);

        $this->saveReadProducts(new ReadProductsEvent([4, 2, 6]));

        $updateDataDestinationProductEventCountHandler->handle()
            ->shouldNotBeCalled();
    }
}
