<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsTransport;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsTransportSpec extends ObjectBehavior
{
    public function let(
        Client $client,
        SenderInterface $sender,
        ReceiverInterface $receiver
    ): void {
        $this->beConstructedWith($client, $sender, $receiver);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GpsTransport::class);
    }

    public function it_is_a_transport(): void
    {
        $this->shouldImplement(TransportInterface::class);
    }

    public function it_is_setupable($client): void
    {
        $this->shouldImplement(SetupableTransportInterface::class);

        $client->setup()
            ->shouldBeCalled();

        $this->setup();
    }

    public function it_sends_a_message($sender): void
    {
        $envelope = new Envelope(new \stdClass());

        $sender->send($envelope)
            ->willReturn($envelope);

        $this->send($envelope);
    }

    public function it_gets_some_messages($receiver): void
    {
        $envelopes = [];

        $receiver->get()
            ->willReturn($envelopes);

        $this->get()
            ->shouldReturn($envelopes);
    }

    public function it_aknowledges_a_message($receiver): void
    {
        $envelope = new Envelope(new \stdClass());

        $receiver->ack($envelope)
            ->shouldBeCalled();

        $this->ack($envelope);
    }

    public function it_rejects_a_message($receiver): void
    {
        $envelope = new Envelope(new \stdClass());

        $receiver->reject($envelope)
            ->shouldBeCalled();

        $this->reject($envelope);
    }
}
