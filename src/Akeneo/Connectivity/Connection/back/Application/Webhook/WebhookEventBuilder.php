<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\WebhookEvent\WebhookEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilder
{
    /** @var iterable<WebhookEventDataBuilder> */
    private $builders;

    /**
     * @param iterable<WebhookEventDataBuilder> $builders
     */
    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    /**
     * @param array{user_id: int} $context
     */
    public function build(BusinessEventInterface $businessEvent, array $context): WebhookEvent
    {
        return new WebhookEvent(
            $businessEvent->name(),
            $businessEvent->uuid(),
            date(\DateTimeInterface::ATOM, $businessEvent->timestamp()),
            $this->buildEventData($businessEvent, $context)
        );
    }

    /**
     * @param array{user_id: int} $context
     *
     * @return array<mixed>
     */
    private function buildEventData(BusinessEventInterface $businessEvent, array $context): array
    {
        foreach ($this->builders as $builder) {
            if (true === $builder->supports($businessEvent)) {
                return $builder->build($businessEvent, $context);
            }
        }

        return $businessEvent->data();
    }
}
