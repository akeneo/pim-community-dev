<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupMustExist extends Constraint
{
    public string $message = 'akeneo_connectivity.connection.connection.constraint.user_group.must_exist';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
