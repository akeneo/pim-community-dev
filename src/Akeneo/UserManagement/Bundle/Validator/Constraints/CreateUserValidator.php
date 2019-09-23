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
        if (preg_match('/\s/', $user->getUsername()) !== 0) {
            $this->context->buildViolation($constraint->errorSpaceInUsername)
                ->atPath('[username]')
                ->addViolation();
        }
    }
}
