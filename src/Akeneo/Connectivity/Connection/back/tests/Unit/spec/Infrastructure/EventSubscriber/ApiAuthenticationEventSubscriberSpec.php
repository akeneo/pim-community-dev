<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepository;
use Akeneo\Tool\Bundle\ApiBundle\EventSubscriber\ApiAuthenticationEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiAuthenticationEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombination,
        SelectConnectionCodeByClientIdQuery $selectConnectionCode,
        WrongCredentialsCombinationRepository $repository
    ): void {
        $this->beConstructedWith($areCredentialsValidCombination, $selectConnectionCode, $repository);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([ApiAuthenticationEvent::class => 'checkCredentialsCombination']);
    }

    public function it_saves_a_wrong_credentials_combination_if_it_is_not_valid(
        $areCredentialsValidCombination,
        $selectConnectionCode,
        $repository
    ): void {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $areCredentialsValidCombination->execute($event->clientId(), $event->username())->willReturn(false);
        $selectConnectionCode->execute($event->clientId())->willReturn('magento');

        $repository->create(Argument::that(function ($arg) {
            return $arg instanceof WrongCredentialsCombination &&
                'magento_0123' === $arg->username() &&
                'magento' === $arg->connectionCode();
        }))->shouldBeCalled();

        $this->checkCredentialsCombination($event);
    }

    public function it_does_nothing_if_combination_is_valid(
        $areCredentialsValidCombination,
        $selectConnectionCode,
        $repository
    ): void {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $areCredentialsValidCombination->execute($event->clientId(), $event->username())->willReturn(true);

        $selectConnectionCode->execute(Argument::cetera())->shouldNotBeCalled();
        $repository->create(Argument::cetera())->shouldNotBeCalled();

        $this->checkCredentialsCombination($event)->shouldReturn(null);
    }
}
