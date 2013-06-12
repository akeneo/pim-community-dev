<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete\Transformer;

interface EntityTransformerInterface
{
    /**
     * Transform entity to array
     *
     * @param object $value
     * @return array
     */
    public function transform($value);
}
