<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeGroups;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MaxAttributeGroupCount extends Constraint
{
    public string $message = 'pim_structure.validation.attribute_groups.max';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
