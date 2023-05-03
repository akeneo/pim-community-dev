<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\Domain;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface PasswordCheckerInterface
{

    /** @param array<mixed> $data */
    public function validatePassword(UserInterface $user, array $data): ConstraintViolationListInterface;
    public function validatePasswordMatch(string $password, string $passwordRepeat, string $propertyPath): ConstraintViolationListInterface;
    public function validatePasswordLength(string $password, string $propertyPath): ConstraintViolationListInterface;
}
