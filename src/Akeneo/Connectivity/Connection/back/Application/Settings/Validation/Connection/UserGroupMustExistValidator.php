<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupMustExistValidator extends ConstraintValidator
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserGroupMustExist) {
            throw new UnexpectedTypeException($constraint, UserGroupMustExist::class);
        }

        if (null !== $value) {
            $userGroup = $this->groupRepository->findOneByIdentifier($value);

            if (null === $userGroup) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }
        }
    }
}
