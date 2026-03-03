<?php

namespace spec\Akeneo\Tool\Bundle\ApiBundle\Security;

use Akeneo\Tool\Component\Api\Event\ApiAuthenticationFailedEvent;
use OAuth2\IOAuth2Storage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OAuth2Spec extends ObjectBehavior
{
    public function let(
        IOAuth2Storage $storage,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->beConstructedWith($storage, $eventDispatcher);
    }

    public function it_dispatches_an_event_when_a_verified_token_is_not_valid(
        EventDispatcherInterface $eventDispatcher,
    ) {
        $eventDispatcher
            ->dispatch(Argument::type(ApiAuthenticationFailedEvent::class))
            ->shouldBeCalled();

        $this
            ->shouldThrow(new HttpException(401, 'The access token provided is invalid.'))
            ->during('verifyAccessToken', ['TpwH4anEPRPwkJN7rLV5T8oMyQN95']);
    }
}
