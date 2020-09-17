<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\WebhookEvent;

use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class WebhookEventBuildingFailedException extends \RuntimeException
{
    private $businessEvent;
    private $context;

    public function __construct(BusinessEventInterface $businessEvent, array $context)
    {
        $this->businessEvent = $businessEvent;
        $this->context = $context;

        parent::__construct('Webhook event building failed');
    }

    public function getContext(): array
    {
        return [
            'business_event' => [
                'name' => $this->businessEvent->name(),
                'author' => $this->businessEvent->author(),
                'uuid' => $this->businessEvent->uuid(),
                'timestamp' => $this->businessEvent->timestamp(),
            ],
            'context' => $this->context,
        ];
    }
}
