<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookEvent\WebhookEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilderSpec extends ObjectBehavior
{
    public function let(WebhookEventDataBuilder $eventDataBuilder1, WebhookEventDataBuilder $eventDataBuilder2): void
    {
        $this->beConstructedWith([$eventDataBuilder1, $eventDataBuilder2]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WebhookEventBuilder::class);
    }

    public function it_builds_a_webhook_event(
        BusinessEventInterface $businessEvent,
        $eventDataBuilder1,
        $eventDataBuilder2
    ): void {
        $businessEvent->name()->willReturn('product.created');
        $businessEvent->uuid()->willReturn('a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $businessEvent->timestamp()->willReturn(1599814161);

        $eventDataBuilder1->supports($businessEvent)->willReturn(false);
        $eventDataBuilder2->supports($businessEvent)->willReturn(true);

        $eventDataBuilder2->build($businessEvent, ['user_id' => 0])->willReturn(['data']);

        $this->build($businessEvent, ['user_id' => 0])
            ->shouldBeLike(new WebhookEvent(
                'product.created',
                'a20832d1-a1e6-4f39-99ea-a1dd859faddb',
                '2020-09-11T08:49:21+00:00',
                ['data']
            ));
    }

    public function it_fallbacks_to_the_business_event_data_if_there_is_no_event_data_builder(
        BusinessEventInterface $businessEvent
    ): void {
        $this->beConstructedWith([]);

        $businessEvent->name()->willReturn('product.created');
        $businessEvent->uuid()->willReturn('a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $businessEvent->timestamp()->willReturn(1599814161);
        $businessEvent->data()->willReturn(['data']);

        $this->build($businessEvent, ['user_id' => 0])
            ->shouldBeLike(new WebhookEvent(
                'product.created',
                'a20832d1-a1e6-4f39-99ea-a1dd859faddb',
                '2020-09-11T08:49:21+00:00',
                ['data']
            ));
    }
}
