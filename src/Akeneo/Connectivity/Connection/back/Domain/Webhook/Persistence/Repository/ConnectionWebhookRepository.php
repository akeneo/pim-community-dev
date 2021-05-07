<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Write\ConnectionWebhook;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConnectionWebhookRepository
{
    public function update(ConnectionWebhook $connectionWebhook): int;
}
