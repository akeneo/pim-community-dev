<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

class FloatValueConverter extends AbstractValueConverter
{
    /**
     * @param float $input
     * @return string
     */
    protected function processConversion($input)
    {
        return (string)$input;
    }
}
