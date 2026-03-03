<?php

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * User validator only at creation
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserValidator extends ConstraintValidator
{
    private const JOB_USERNAME_PREFIX = 'job_automated_';

    /**
     * @param UserInterface         $user
     * @param Constraint|CreateUser $constraint
     */
    public function validate($user, Constraint $constraint)
    {
        if ($this->isUserCreated($user)) {
            return;
        }

        $this->validateUsername($user, $constraint);
    }

    private function isUserCreated(UserInterface $user): bool
    {
        return null !== $user->getId();
    }

    private function validateUsername(UserInterface $user, CreateUser $constraint)
    {
        $username = $user->getUserIdentifier();
        if (preg_match('/\s/', $username) !== 0) {
            $this->context->buildViolation($constraint->errorSpaceInUsername)
                ->atPath('username')
                ->addViolation();
        }

        if (!$user->isJobUser() && str_starts_with($username, self::JOB_USERNAME_PREFIX)) {
            $this->context->buildViolation(CreateUser::RESERVED_PREFIX_USERNAME, ['{{ prefix }}' => self::JOB_USERNAME_PREFIX])
                ->atPath('username')
                ->addViolation();
        }
    }
}
