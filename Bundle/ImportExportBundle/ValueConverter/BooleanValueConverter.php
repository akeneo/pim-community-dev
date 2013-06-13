<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

class BooleanValueConverter extends AbstractValueConverter
{
    /**
     * @param boolean $input
     * @return string
     */
    protected function processConversion($input)
    {
        return $input ? '1' : '0';
    }
}
