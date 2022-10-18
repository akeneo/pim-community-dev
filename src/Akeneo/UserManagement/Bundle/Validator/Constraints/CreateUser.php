<?php

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for user creation
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUser extends Constraint
{
    public string $errorSpaceInUsername = 'The username should not contain space character.';
    public const RESERVED_PREFIX_USERNAME = 'pim_user.reserved_prefix_username';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
