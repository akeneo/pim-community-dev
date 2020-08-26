<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilderRegistry
{
    /** @var WebhookEventDataBuilder[] */
    private static $builders;

    public function build(ConnectionWebhook $webhook, BusinessEventInterface $businessEvent): array
    {
        foreach (self::$builders as $builder) {
            if ($builder->supports($businessEvent)) {
                return $builder->build($webhook, $businessEvent);
            }
        }

        return $businessEvent->getData();
    }

    public function register(WebhookEventDataBuilder $builder): void
    {
        self::$builders[] = $builder;
    }
}
