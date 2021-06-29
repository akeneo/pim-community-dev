<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRolePrivileges extends Constraint
{
    public string $message = 'The following permissions are invalid: {{ invalid_permissions }}';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): array
    {
        return [Constraint::PROPERTY_CONSTRAINT];
    }
}
