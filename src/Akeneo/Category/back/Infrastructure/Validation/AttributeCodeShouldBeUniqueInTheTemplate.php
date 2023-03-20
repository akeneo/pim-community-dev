<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AttributeCodeShouldBeUniqueInTheTemplate extends Constraint
{
    public string $message = 'akeneo.category.validation.attribute.code.unique_in_the_template';
}
