<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateUserGroupAttributeGroupsPermissions extends Constraint
{
    public string $message = 'attribute_group.permissions.validation.invalid';

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return [
            new Assert\NotNull(),
            new Assert\Type('array'),
            new Assert\Collection([
                'user_group' => [
                    new Assert\NotNull(),
                    new Assert\Type('string'),
                ],
                'permissions' => [
                    new Assert\NotNull(),
                    new Assert\Type('array'),
                    new Assert\Collection([
                        'edit' => $this->getPermissionLevelConstraint(),
                        'view' => $this->getPermissionLevelConstraint(),
                    ]),
                    new Assert\Callback(function ($permissions, ExecutionContextInterface $context) {
                        $editAll = $permissions['edit']['all'] ?? false;
                        $viewAll = $permissions['view']['all'] ?? false;

                        if ($editAll === true && $viewAll === false) {
                            $context->buildViolation($this->message)->addViolation();
                        }
                    }),
                ],
            ]),
        ];
    }

    /**
     * @return Constraint[]
     */
    private function getPermissionLevelConstraint(): array
    {
        return [
            new Assert\Collection([
                'all' => [
                    new Assert\NotNull(),
                    new Assert\Type('bool'),
                ],
                'identifiers' => [
                    new Assert\NotNull(),
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Type('string'),
                    ]),
                ],
            ]),
            new Assert\Callback(function ($level, ExecutionContextInterface $context) {
                $all = $level['all'] ?? false;
                $identifiers = $level['identifiers'] ?? [];

                if (true === $all && !empty($identifiers)) {
                    $context->buildViolation($this->message)
                        ->addViolation();
                }
            }),
        ];
    }
}
