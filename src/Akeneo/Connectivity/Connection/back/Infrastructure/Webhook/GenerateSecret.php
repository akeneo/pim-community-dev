<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateSecret implements GenerateSecretInterface
{
    public function generate(): string
    {
        $bytes = random_bytes(32);

        return base_convert(bin2hex($bytes), 16, 36);
    }
}
