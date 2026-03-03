<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SaveWebhookSecretQueryInterface
{
    public function execute(string $code, string $secret): bool;
}
