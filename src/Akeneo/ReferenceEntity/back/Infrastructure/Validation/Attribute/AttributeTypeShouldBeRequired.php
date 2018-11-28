<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeTypeShouldBeRequired extends Constraint
{
    public const ERROR_MESSAGE = 'pim_reference_entity.attribute.validation.type.should_be_required';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_referenceentity.infrastructure.validation.attribute.attribute_type_should_be_required';
    }
}

