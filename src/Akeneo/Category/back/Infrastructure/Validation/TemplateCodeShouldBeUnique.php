<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[\Attribute]
class TemplateCodeShouldBeUnique extends Constraint
{
    public string $message = 'akeneo.category.validation.template.code.unique';

    public function validatedBy(): string
    {
        return TemplateCodeShouldBeUniqueValidator::class;
    }
}
