<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class BlacklistedAttributeCode extends Constraint
{
    /**
     * Violation message for blacklisted attribute identifiers
     */
    public string $message = 'pim_catalog.constraint.blacklisted_attribute_code';
    public string $internalAPIMessage = 'pim_catalog.constraint.blacklisted_attribute_code_with_link';

    public function validatedBy()
    {
        return 'pim_blacklisted_attribute_code_validator';
    }
}
