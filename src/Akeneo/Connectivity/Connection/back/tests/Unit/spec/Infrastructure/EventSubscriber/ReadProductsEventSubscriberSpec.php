<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountCommand;
use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\ReadProductsEventSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReadProductsEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombinationQuery,
        SelectConnectionCodeByClientIdQuery $selectConnectionCodeQuery,
        ConnectionRepository $connectionRepository,
        UpdateDataDestinationProductEventCountHandler $updateDataDestinationProductEventCountHandler
    ): void {
        $this->beConstructedWith(
            $areCredentialsValidCombinationQuery,
            $selectConnectionCodeQuery,
            $connectionRepository,
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
        $this->getSubscribedEvents()
            ->shouldReturn([
                ApiAuthenticationEvent::class => 'checkApiCredentialsCombination',
                ReadProductsEvent::class => 'saveReadProducts',
            ]);
    }

    public function it_saves_read_products_events(
        $areCredentialsValidCombinationQuery,
        $selectConnectionCodeQuery,
        $connectionRepository,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection
    ): void {
        $connection->code()
            ->willReturn(new ConnectionCode('ecommerce'));
        $connection->flowType()
            ->willReturn(new FlowType(FlowType::DATA_DESTINATION));

        $areCredentialsValidCombinationQuery->execute('3', 'ecommerce_0123')
            ->willReturn(true);
        $selectConnectionCodeQuery->execute('3')
            ->willReturn('ecommerce');
        $connectionRepository->findOneByCode('ecommerce')
            ->willReturn($connection);

        $this->checkApiCredentialsCombination(new ApiAuthenticationEvent('ecommerce_0123', '3'));
        $this->saveReadProducts(new ReadProductsEvent([4, 2, 6]));

        $updateDataDestinationProductEventCountHandler->handle(
            new UpdateDataDestinationProductEventCountCommand(
                'ecommerce',
                HourlyInterval::createFromDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                3
            )
        )->shouldBeCalled();
    }

    public function it_does_not_save_read_products_events_for_invalid_connection_credentials_combination(
        $areCredentialsValidCombinationQuery,
        $updateDataDestinationProductEventCountHandler
    ): void {
        $areCredentialsValidCombinationQuery->execute('2', 'ecommerce_0123')
            ->willReturn(false);

        $this->checkApiCredentialsCombination(new ApiAuthenticationEvent('ecommerce_0123', '2'));
        $this->saveReadProducts(new ReadProductsEvent([4, 2, 6]));

        $updateDataDestinationProductEventCountHandler->handle()
            ->shouldNotBeCalled();
    }

    public function it_does_not_save_read_products_events_when_the_connection_flow_type_is_different_from_destination(
        $areCredentialsValidCombinationQuery,
        $selectConnectionCodeQuery,
        $connectionRepository,
        $updateDataDestinationProductEventCountHandler,
        Connection $connection
    ): void {
        $connection->code()
            ->willReturn(new ConnectionCode('ecommerce'));
        $connection->flowType()
            ->willReturn(new FlowType(FlowType::DATA_SOURCE));

        $areCredentialsValidCombinationQuery->execute('3', 'ecommerce_0123')
            ->willReturn(true);
        $selectConnectionCodeQuery->execute('3')
            ->willReturn('ecommerce');
        $connectionRepository->findOneByCode('ecommerce')
            ->willReturn($connection);

        $this->checkApiCredentialsCombination(new ApiAuthenticationEvent('ecommerce_0123', '3'));
        $this->saveReadProducts(new ReadProductsEvent([4, 2, 6]));

        $updateDataDestinationProductEventCountHandler->handle()
            ->shouldNotBeCalled();
    }
}
