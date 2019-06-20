<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntityPermission;

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\SetPermissions\SetReferenceEntityPermissionsCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AtLeastOneUserGroupShouldHaveEditPermissionValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof SetReferenceEntityPermissionsCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    SetReferenceEntityPermissionsCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AtLeastOneUserGroupShouldHaveEditPermission) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(SetReferenceEntityPermissionsCommand $command): void
    {
        $canEdit = false;

        foreach ($command->permissionsByUserGroup as $userGroupPermissionCommand) {
            $canEdit = $canEdit || $userGroupPermissionCommand->rightLevel === 'edit';
        }

        if (!$canEdit) {
            $this->context->buildViolation(AtLeastOneUserGroupShouldHaveEditPermission::ERROR_MESSAGE)
                ->addViolation();
        }
    }
}
