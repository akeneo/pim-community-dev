<?php

namespace Oro\Bundle\FormBundle\DataTransformer;

interface EntityTransformerInterface
{
    /**
     * Transform entity to string based on autocompleter configuration.
     *
     * @param string $alias
     * @param object $value
     * @return string
     */
    public function transform($alias, $value);
}
