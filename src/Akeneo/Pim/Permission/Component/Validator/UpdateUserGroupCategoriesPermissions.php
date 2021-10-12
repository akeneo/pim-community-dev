<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateUserGroupCategoriesPermissions extends Constraint
{
    public string $message = 'category.permissions.validation.invalid';

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
                        'own' => $this->getPermissionLevelConstraint(),
                        'edit' => $this->getPermissionLevelConstraint(),
                        'view' => $this->getPermissionLevelConstraint(),
                    ]),
                    new Assert\Callback(function ($permissions, ExecutionContextInterface $context) {
                        $own = $permissions['own']['all'] ?? null;
                        $edit = $permissions['edit']['all'] ?? null;
                        $view = $permissions['view']['all'] ?? null;

                        if ($own === true && $edit === false) {
                            $context->buildViolation($this->message)
                                ->addViolation();
                        }

                        if ($edit === true && $view === false) {
                            $context->buildViolation($this->message)
                                ->addViolation();
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
