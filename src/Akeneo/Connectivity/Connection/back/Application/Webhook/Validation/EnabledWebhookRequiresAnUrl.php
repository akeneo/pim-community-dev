<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledWebhookRequiresAnUrl extends Constraint
{
    public string $message = 'akeneo_connectivity.connection.webhook.error.required';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
