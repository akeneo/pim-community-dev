<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Write\ReadProducts;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\ReadProductRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\KernelEvents;

class ReadProductsEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombinationQuery,
        SelectConnectionCodeByClientIdQuery $selectConnectionCodeQuery,
        ConnectionRepository $connectionRepository,
        ReadProductRepository $readProductRepository
    ): void {
        $this->beConstructedWith(
            $areCredentialsValidCombinationQuery,
            $selectConnectionCodeQuery,
            $connectionRepository,
            $readProductRepository
        );
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()
            ->shouldReturn([
                ApiAuthenticationEvent::class => 'onApiAuthentication',
                ReadProductsEvent::class => 'onProductsRead',
                KernelEvents::TERMINATE => 'onKernelTerminate'
            ]);
    }

    public function it_saves_read_products_events(
        $areCredentialsValidCombinationQuery,
        $selectConnectionCodeQuery,
        $connectionRepository,
        $readProductRepository,
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

        $this->onApiAuthentication(new ApiAuthenticationEvent('ecommerce_0123', '3'));
        $this->onProductsRead(new ReadProductsEvent([4, 2, 6]));
        $this->onKernelTerminate();

        $readProductRepository->bulkInsert(Argument::that(function (ReadProducts $readProducts) {
            return $this->assertEqualsReadProducts(
                new ReadProducts('ecommerce', [4, 2, 6], new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
                $readProducts
            );
        }))
            ->shouldBeCalled();
    }

    public function it_does_not_save_read_products_events_for_invalid_connection_credentials_combination(
        $areCredentialsValidCombinationQuery,
        $readProductRepository
    ): void {
        $areCredentialsValidCombinationQuery->execute('2', 'ecommerce_0123')
            ->willReturn(false); // <- credentials combination is invalid

        $this->onApiAuthentication(new ApiAuthenticationEvent('ecommerce_0123', '2'));
        $this->onProductsRead(new ReadProductsEvent([4, 2, 6]));
        $this->onKernelTerminate();

        $readProductRepository->bulkInsert()
            ->shouldNotBeCalled();
    }

    public function it_does_not_save_read_products_events_when_the_connection_flow_type_is_different_from_destination(
        $areCredentialsValidCombinationQuery,
        $selectConnectionCodeQuery,
        $connectionRepository,
        $readProductRepository,
        Connection $connection
    ): void {
        $connection->code()
            ->willReturn(new ConnectionCode('ecommerce'));
        $connection->flowType()
            ->willReturn(new FlowType(FlowType::DATA_SOURCE)); // <- flow_type is DATA_SOURCE

        $areCredentialsValidCombinationQuery->execute('3', 'ecommerce_0123')
            ->willReturn(true);
        $selectConnectionCodeQuery->execute('3')
            ->willReturn('ecommerce');
        $connectionRepository->findOneByCode('ecommerce')
            ->willReturn($connection);

        $this->onApiAuthentication(new ApiAuthenticationEvent('ecommerce_0123', '3'));
        $this->onProductsRead(new ReadProductsEvent([4, 2, 6]));
        $this->onKernelTerminate();

        $readProductRepository->bulkInsert()
            ->shouldNotBeCalled();
    }

    private function assertEqualsReadProducts(ReadProducts $expected, ReadProducts $actual): bool
    {
        return $expected->connectionCode() === $actual->connectionCode()
            && $expected->productIds() === $actual->productIds()
            && $expected->eventDatetime()->format('U') === $actual->eventDatetime()->format('U');
    }
}
