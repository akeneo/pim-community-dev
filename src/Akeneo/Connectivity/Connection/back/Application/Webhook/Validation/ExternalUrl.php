<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalUrl extends Constraint
{
    public string $message = 'akeneo_connectivity.connection.webhook.error.not_allowed_url';
}
