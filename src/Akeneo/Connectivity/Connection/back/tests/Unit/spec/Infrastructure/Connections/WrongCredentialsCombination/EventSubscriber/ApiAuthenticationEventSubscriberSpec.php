<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiAuthenticationEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        ConnectionContext $connectionContext,
        WrongCredentialsCombinationRepositoryInterface $repository
    ): void {
        $this->beConstructedWith($connectionContext, $repository);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([ApiAuthenticationEvent::class => 'checkCredentialsCombination']);
    }

    public function it_saves_a_wrong_credentials_combination_if_it_is_not_valid(
        $connectionContext,
        $repository
    ): void {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $connectionContext->areCredentialsValidCombination()->willReturn(false);

        $connection = new Connection('magento', 'magento', FlowType::DATA_DESTINATION, 42, 10);
        $connectionContext->getConnection()->willReturn($connection);

        $repository->create(Argument::that(fn ($arg): bool => $arg instanceof WrongCredentialsCombination &&
            'magento_0123' === $arg->username() &&
            'magento' === $arg->connectionCode()))->shouldBeCalled();

        $this->checkCredentialsCombination($event);
    }

    public function it_does_nothing_if_combination_is_valid(
        $connectionContext,
        $repository
    ): void {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $connectionContext->areCredentialsValidCombination()->willReturn(true);

        $connectionContext->getConnection()->shouldNotBeCalled();
        $repository->create(Argument::cetera())->shouldNotBeCalled();

        $this->checkCredentialsCombination($event)->shouldReturn(null);
    }

    public function it_does_nothing_if_connection_is_null(
        $connectionContext,
        $repository
    ): void {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $connectionContext->areCredentialsValidCombination()->willReturn(true);

        $connectionContext->getConnection()->willReturn(null);
        $repository->create(Argument::cetera())->shouldNotBeCalled();

        $this->checkCredentialsCombination($event)->shouldReturn(null);
    }
}
