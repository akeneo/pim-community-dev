<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

interface ConverterInterface
{
    /**
     * Converts item into an array that represents it in view.
     *
     * @param mixed $item
     * @return array
     */
    public function convertItem($item);
}
