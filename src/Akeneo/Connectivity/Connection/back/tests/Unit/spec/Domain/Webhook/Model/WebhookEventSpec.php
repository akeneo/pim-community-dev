<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookEventSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'product.created',
            '21f7f779-f094-4305-8ee4-65fdddd5a418',
            '2020-01-01T00:00:00+00:00',
            ['data']
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WebhookEvent::class);
    }

    public function it_returns_an_action(): void
    {
        $this->action()
            ->shouldReturn('product.created');
    }

    public function it_returns_an_event_id(): void
    {
        $this->eventId()
            ->shouldReturn('21f7f779-f094-4305-8ee4-65fdddd5a418');
    }

    public function it_returns_an_event_date(): void
    {
        $this->eventDate()
            ->shouldReturn('2020-01-01T00:00:00+00:00');
    }

    public function it_returns_data(): void
    {
        $this->data()
            ->shouldReturn(['data']);
    }
}
