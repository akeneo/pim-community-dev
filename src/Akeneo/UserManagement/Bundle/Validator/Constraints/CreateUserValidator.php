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
     * @param UserInterface $user
     * @param Constraint    $constraint
     */
    public function validate($user, Constraint $constraint)
    {
        if (null !== $user->getId()) {
            return;
        }

        $this->validateUsername($user, $constraint);
    }

    protected function validateUsername(UserInterface $user, CreateUser $constraint)
    {
        if (preg_match('/\s/', $user->getUsername()) !== 0) {
            $this->context->buildViolation($constraint->errorSpaceInUsername)
                ->addViolation();
        }
    }
}
