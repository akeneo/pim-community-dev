<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\WebhookEventBuilder;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEventDataBuilder;
use Akeneo\Pim\Enrichment\Bundle\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Bundle\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEventDataBuilder implements WebhookEventDataBuilder
{
    public function build(ConnectionWebhook $webhook, BusinessEventInterface $businessEvent)
    {
        return $businessEvent->data();
    }

    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductUpdated || $businessEvent instanceof ProductCreated;
    }
}
