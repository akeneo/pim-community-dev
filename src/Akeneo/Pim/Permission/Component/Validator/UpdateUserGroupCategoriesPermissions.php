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
                        'own' => new Assert\Collection([
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
                        'edit' => new Assert\Collection([
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
                        'view' => new Assert\Collection([
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
                    ]),
                    new Assert\Callback(function ($permissions, ExecutionContextInterface $context) {
                        if ($permissions['own']['all'] ?? null === true && $permissions['edit']['all'] ?? null === false) {
                            $context->buildViolation($this->message)
                                ->addViolation();
                        }

                        if ($permissions['edit']['all'] ?? null === true && $permissions['view']['all'] ?? null === false) {
                            $context->buildViolation($this->message)
                                ->addViolation();
                        }
                    }),
                ],
            ]),
        ];
    }
}
