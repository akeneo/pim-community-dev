<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsSender;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\Topic;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsSenderSpec extends ObjectBehavior
{
    public function let(Topic $topic, SerializerInterface $serializer, OrderingKeySolver $orderingKeySolver)
    {
        $this->beConstructedWith($topic, $serializer, $orderingKeySolver);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GpsSender::class);
    }

    public function it_sends_a_message(
        Topic $topic,
        SerializerInterface $serializer,
        OrderingKeySolver $orderingKeySolver
    ): void {
        $envelope = new Envelope((object)['message' => 'My message!']);

        $serializer->encode($envelope)
            ->willReturn([
                'body' => 'My message!',
                'headers' => ['my_attribute' => 'My attribute!']
            ]);
        $orderingKeySolver->solve($envelope)->willReturn(null);

        $topic->publish([
            'data' => 'My message!',
            'attributes' => ['my_attribute' => 'My attribute!'],
        ])
            ->shouldBeCalled();

        $this->send($envelope);
    }

    public function it_sends_a_message_with_ordering_key(
        Topic $topic,
        SerializerInterface $serializer,
        OrderingKeySolver $orderingKeySolver
    ): void {
        $envelope = new Envelope(new \stdClass);

        $serializer->encode($envelope)->willReturn([
            'body' => 'My message!',
            'headers' => ['my_attribute' => 'My attribute!'],
        ]);
        $orderingKeySolver->solve($envelope)->willReturn('a_key');

        $topic->publish([
            'data' => 'My message!',
            'attributes' => ['my_attribute' => 'My attribute!'],
            'orderingKey' => 'a_key',
        ])->shouldBeCalled();

        $this->send($envelope);
    }

    public function it_throws_a_transport_exception_if_an_error_is_raised_while_sending_a_message(
        $topic,
        $serializer
    ): void {
        $envelope = new Envelope((object)['message' => 'My message!']);

        $serializer->encode($envelope)
            ->willReturn([
                'body' => 'My message!',
                'headers' => ['my_attribute' => 'My attribute!']
            ]);

        $topic->publish([
            'data' => 'My message!',
            'attributes' => ['my_attribute' => 'My attribute!'],
        ])
            ->willThrow(GoogleException::class);

        $this->shouldThrow(TransportException::class)
            ->during('send', [$envelope]);
    }
}
