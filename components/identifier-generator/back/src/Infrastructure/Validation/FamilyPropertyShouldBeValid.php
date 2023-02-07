<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyPropertyShouldBeValid extends Constraint
{
    public string $fieldsRequired = 'validation.identifier_generator.family_property_fields_required';

    public string $processTypeNoOtherProperties = 'validation.identifier_generator.process_type_no_other_properties';
}
