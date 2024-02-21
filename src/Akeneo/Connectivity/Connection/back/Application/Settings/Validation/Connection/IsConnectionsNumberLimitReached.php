<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReached extends Constraint
{
    public string $message = 'akeneo_connectivity.connection.connection.constraint.connections_number_limit_reached';

    public function getTargets(): string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
