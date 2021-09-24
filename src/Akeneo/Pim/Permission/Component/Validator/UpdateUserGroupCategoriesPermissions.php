<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;

class UpdateUserGroupCategoriesPermissions extends Constraint
{
    public string $invalid_structure = 'category.permissions.validation.invalid_structure';
}
