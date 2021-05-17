<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Command;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateWebhookSecretCommand
{
    private string $connectionCode;

    public function __construct(string $connectionCode)
    {
        $this->connectionCode = $connectionCode;
    }

    public function connectionCode(): string
    {
        return $this->connectionCode;
    }
}
