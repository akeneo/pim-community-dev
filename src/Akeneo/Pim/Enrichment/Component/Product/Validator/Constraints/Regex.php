<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Regex as BaseConstraint;

class Regex extends BaseConstraint
{
    /** @var string */
    public $message = 'The {{ attribute_code }} attribute must match the following regular expression: {{ pattern }}.';

    /** @var string */
    public $attributeCode;

    public function getRequiredOptions(): array
    {
        return ['pattern', 'attributeCode'];
    }
}
