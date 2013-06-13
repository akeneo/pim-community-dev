<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

abstract class AbstractValueConverter implements ValueConverterInterface
{
    /**
     * @param mixed $input
     * @return string|null
     */
    public function convertToString($input = null)
    {
        if (null === $input) {
            return null;
        }

        return $this->processConversion($input);
    }

    /**
     * @param mixed $input
     * @return string|null
     */
    abstract protected function processConversion($input);
}
