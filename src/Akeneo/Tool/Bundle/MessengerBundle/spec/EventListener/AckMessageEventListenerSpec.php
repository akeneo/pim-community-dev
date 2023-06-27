<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AckMessageEventListenerSpec extends ObjectBehavior
{
    function it_acks_message_when_receiver_name_is_matching(
        ContainerInterface $receiverLocator,
        ReceiverInterface $receiver
    ) {
        $this->beConstructedWith($receiverLocator, 'receiver-name');

        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver-name');

        $this->receiverLocator->get('receiver-name')->willReturn($receiver);
        $receiver->ack($envelope)->shouldBeCalledOnce();

        $this->ackMessage($event);
    }

    function it_does_not_ack_message_when_receiver_name_doesnt_match(ContainerInterface $receiverLocator)
    {
        $this->beConstructedWith($receiverLocator, 'receiver-name');

        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageReceivedEvent($envelope, 'other-receiver-name');

        $this->receiverLocator->get(Argument::any())->shouldNotBeCalled();

        $this->ackMessage($event);
    }
}
