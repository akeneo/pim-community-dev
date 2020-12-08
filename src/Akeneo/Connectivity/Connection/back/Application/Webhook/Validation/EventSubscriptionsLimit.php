<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventSubscriptionsLimit extends Constraint
{
    public string $message = 'akeneo_connectivity.connection.webhook.error.limit_reached';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
