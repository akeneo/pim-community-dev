<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\NotBlank as BaseConstraint;

class NotBlank extends BaseConstraint
{
    /** @var string */
    public $message = 'The {{ attribute_code }} attribute cannot be empty.';

    /** @var string */
    public $attributeCode;

    public function getRequiredOptions(): array
    {
        return ['attributeCode'];
    }
}
