<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

class StringValueConverter extends AbstractValueConverter
{
    /**
     * @param string $input
     * @return string
     */
    protected function processConversion($input)
    {
        return $input;
    }
}
