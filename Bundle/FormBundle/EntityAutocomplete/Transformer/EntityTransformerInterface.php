<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Transformer;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;

interface EntityTransformerInterface
{
    /**
     * Transform entity to string based on autocompleter configuration.
     *
     * @param object $value
     * @param Property[] $properties
     * @return string
     */
    public function transform($value, array $properties);
}
