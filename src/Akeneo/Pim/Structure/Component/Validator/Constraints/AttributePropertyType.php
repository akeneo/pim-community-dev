<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributePropertyType extends Constraint
{
    public string $message = '';
    public array $properties;
    public string $type;

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['properties', 'type'];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_structure_attribute_property_type_validator';
    }
}
