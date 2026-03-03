<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\CustomApps\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[\Attribute]
class IsCustomAppsNumberLimitReached extends Constraint
{
    public string $message = 'akeneo_connectivity.connection.connect.custom_apps.create_modal.errors.limit_reached';

    public function getTargets(): string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
