<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UpdateUserGroupCategoriesPermissionsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UpdateUserGroupCategoriesPermissions) {
            throw new UnexpectedTypeException($constraint, UpdateUserGroupCategoriesPermissions::class);
        }

        $permissionsConstraints = new Assert\Collection([
            'all' => [
                new Assert\Type('bool'),
            ],
            'identifiers' => [
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                ]),
            ],
        ]);

        $constraints = [
            new Assert\Collection([
                'user_group' => [
                    new Assert\Type('string'),
                ],
                'permissions' => new Assert\Collection([
                    'own' => $permissionsConstraints,
                    'edit' => $permissionsConstraints,
                    'view' => $permissionsConstraints,
                ]),
            ]),
        ];

        $errors = $this->context->getValidator()->validate($value, $constraints);

        if (0 < $errors->count()) {
            $this->context->buildViolation($errors->get(0)->getMessage())->addViolation();
        }
    }
}
