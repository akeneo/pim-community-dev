<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Infrastructure;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Domain\PasswordCheckerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class PasswordChecker implements PasswordCheckerInterface
{
    private const PASSWORD_MINIMUM_LENGTH = 8;
    private const PASSWORD_MAXIMUM_LENGTH = 4096;
    public function __construct(
        private readonly UserPasswordHasherInterface $encoder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validatePassword(UserInterface $user, array $data): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $newPasswordRepeat = $data['new_password_repeat'] ?? '';

        if (!$this->encoder->isPasswordValid($user, $currentPassword)) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.current_password.wrong'),
                '',
                [],
                '',
                'current_password',
                ''
            ));
        }

        $violations->addAll($this->validatePasswordLength($newPassword, 'new_password'));
        $violations->addAll($this->validatePasswordMatch($newPassword, $newPasswordRepeat, 'new_password_repeat'));

        return $violations;
    }

    public function validatePasswordMatch(string $password, string $passwordRepeat, string $propertyPath): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if ($password !== $passwordRepeat) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.new_password_repeat.not_match'),
                '',
                [],
                '',
                $propertyPath,
                ''
            ));
        }

        return $violations;
    }

    public function validatePasswordLength(string $password, string $propertyPath): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if (self::PASSWORD_MINIMUM_LENGTH > mb_strlen($password)) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.new_password.minimum_length'),
                '',
                [],
                '',
                $propertyPath,
                ''
            ));
        // We have to use `strlen` here because Symfony's BasePasswordEncoder will check
        // the actual byte count when trying to encode it with salt.
        // See: Symfony\Component\Security\Core\Encoder\BasePasswordEncoder
        } elseif (self::PASSWORD_MAXIMUM_LENGTH < strlen($password)) {
            $violations->add(new ConstraintViolation(
                $this->translator->trans('pim_user.user.fields_errors.new_password.maximum_length'),
                '',
                [],
                '',
                $propertyPath,
                ''
            ));
        }

        return $violations;
    }
}
