<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsSender;
use Google\Cloud\PubSub\Topic;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsSenderSpec extends ObjectBehavior
{
    public function let(Topic $topic, SerializerInterface $serializer)
    {
        $this->beConstructedWith($topic, $serializer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GpsSender::class);
    }

    public function it_sends_a_message($topic, $serializer): void
    {
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
            ->shouldBeCalled();

        $this->send($envelope);
    }
}
