<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\ConnectionWebhook;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WebhookEventDataBuilder
{
    public function build(ConnectionWebhook $webhook, BusinessEventInterface $businessEvent);

    public function supports(BusinessEventInterface $businessEvent): bool;
}
