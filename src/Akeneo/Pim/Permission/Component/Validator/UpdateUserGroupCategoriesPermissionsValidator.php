<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

class UpdateUserGroupCategoriesPermissionsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UpdateUserGroupCategoriesPermissions) {
            throw new UnexpectedTypeException($constraint, UpdateUserGroupCategoriesPermissions::class);
        }

        try {
            $this->validateStructure($value);
        } catch (\InvalidArgumentException $e) {
            $this->context->buildViolation($constraint->invalid_structure)->addViolation();
        }
    }

    private function validateStructure($value): void
    {
        Assert::isArray($value);
        Assert::same(array_keys($value), ['user_group', 'permissions']);
        Assert::string($value['user_group']);

        $permissions = $value['permissions'];
        Assert::isArray($permissions);
        Assert::same(array_keys($permissions), ['own', 'edit', 'view']);

        foreach ($permissions as $permission) {
            Assert::same(array_keys($permission), ['all', 'identifiers']);
            Assert::boolean($permission['all']);
            Assert::isArray($permission['identifiers']);

            if ($permission['all'] === true) {
                Assert::isEmpty($permission['identifiers']);
            }

            foreach ($permission['identifiers'] as $identifier) {
                Assert::string($identifier);
            }
        }

        if ($permissions['own']['all'] === true) {
            Assert::true($permissions['edit']['all']);
        }

        if ($permissions['edit']['all'] === true) {
            Assert::true($permissions['view']['all']);
        }
    }
}
