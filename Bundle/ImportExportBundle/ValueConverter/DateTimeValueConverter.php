<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

class DateTimeValueConverter extends AbstractValueConverter
{
    /**
     * @param \DateTime $input
     * @return string
     */
    protected function processConversion($input)
    {
        if ($input instanceof \DateTime) {
            return $input->format(\DateTime::W3C);
        }

        return (string)$input;
    }
}
