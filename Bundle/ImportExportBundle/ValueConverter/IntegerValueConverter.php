<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

class IntegerValueConverter extends AbstractValueConverter
{
    /**
     * @param int $input
     * @return string
     */
    protected function processConversion($input)
    {
        return (string)$input;
    }
}
