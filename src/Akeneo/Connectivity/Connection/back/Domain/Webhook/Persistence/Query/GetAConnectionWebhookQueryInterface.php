<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAConnectionWebhookQueryInterface
{
    public function execute(string $code): ?ConnectionWebhook;
}
