<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\WebhookEventBuilder;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Webhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Pim\Enrichment\Bundle\Message\BusinessEvent;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdatedWebhookEventBuilder
{
    // TODO use business event interface
    public function build(Webhook $webhook, BusinessEvent $businessEvent): WebhookEvent
    {
        
    }
}
