<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\NativeMessageStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsReceiver;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsReceiverSpec extends ObjectBehavior
{
    public function let(Subscription $subscription, SerializerInterface $serializer)
    {
        $this->beConstructedWith($subscription, $serializer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GpsReceiver::class);
    }

    public function it_gets_messages($subscription, $serializer): void
    {
        $gpsMessage = new Message(
            [
                'data' => 'My message!',
                'messageId' => '123',
                'attributes' => ['my_attribute' => 'My attribute!']
            ]
        );
        $envelope = new Envelope((object)['message' => 'My message!']);

        $subscription->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ])
            ->willReturn([$gpsMessage]);

        $serializer->decode([
            'body' => 'My message!',
            'headers' => ['my_attribute' => 'My attribute!']
        ])
            ->willReturn($envelope);

        $this->get()
            ->shouldBeLike([
                $envelope
                    ->with(new TransportMessageIdStamp('123'))
                    ->with(new NativeMessageStamp($gpsMessage))
            ]);
    }

    public function it_ackownledges_a_message($subscription): void
    {
        $gpsMessage = new Message(
            [
                'data' => 'My message!',
            ]
        );

        $envelope = new Envelope((object)['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);

        $subscription->acknowledge($gpsMessage)
            ->shouldBeCalled();

        $this->ack($envelope);
    }

    public function it_rejects_a_message($subscription): void
    {
        $gpsMessage = new Message(
            [
                'data' => 'My message!',
            ]
        );

        $envelope = new Envelope((object)['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);

        $subscription->acknowledge($gpsMessage)
            ->shouldBeCalled();

        $this->reject($envelope);
    }

    public function it_throws_a_transport_exception_if_an_error_is_raised_while_fetching_a_message($subscription): void
    {
        $subscription->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ])
            ->willThrow(GoogleException::class);

        $this->shouldThrow(TransportException::class)
            ->during('get');
    }

    public function it_throws_a_transport_exception_if_an_error_is_raised_while_acknowledging_a_message(
        $subscription
    ): void {
        $gpsMessage = new Message(
            [
                'data' => 'My message!',
            ]
        );

        $envelope = new Envelope((object)['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);

        $subscription->acknowledge($gpsMessage)
            ->willThrow(GoogleException::class);

        $this->shouldThrow(TransportException::class)
            ->during('ack', [$envelope]);
    }

    public function it_throws_a_transport_exception_if_an_error_is_raised_while_rejecting_a_message($subscription): void
    {
        $gpsMessage = new Message(
            [
                'data' => 'My message!',
            ]
        );

        $envelope = new Envelope((object)['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);

        $subscription->acknowledge($gpsMessage)
            ->willThrow(GoogleException::class);

        $this->shouldThrow(TransportException::class)
            ->during('reject', [$envelope]);
    }
}
