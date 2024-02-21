<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIdMustBeValidValidator extends ConstraintValidator
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UserIdMustBeValid) {
            throw new UnexpectedTypeException($constraint, UserIdMustBeValid::class);
        }

        /** @var UserInterface|null $user */
        $user = $this->userRepository->find($value);

        if (null === $user) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
