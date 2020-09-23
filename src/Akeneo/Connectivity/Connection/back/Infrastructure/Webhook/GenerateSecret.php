<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;
use Ramsey\Uuid\Uuid;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateSecret implements GenerateSecretInterface
{
    public function generate(): string
    {
        try {
            return Uuid::uuid4()->toString();
        } catch (\Exception $exception) {
            return uniqid();
        }
    }
}
