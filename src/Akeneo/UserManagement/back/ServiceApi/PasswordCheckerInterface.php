<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\ServiceApi;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface PasswordCheckerInterface
{
    public function validatePassword(UserInterface $user, array $data): ConstraintViolationListInterface;
    public function validatePasswordMatch(string $password, string $passwordRepeat, string $propertyPath): ConstraintViolationListInterface;
    public function validatePasswordLength(string $password, string $propertyPath): ConstraintViolationListInterface;
}
