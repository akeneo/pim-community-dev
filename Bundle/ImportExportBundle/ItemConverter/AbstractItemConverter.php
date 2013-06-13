<?php

namespace Oro\Bundle\ImportExportBundle\ItemConverter;

abstract class AbstractItemConverter implements ItemConverterInterface
{
    /**
     * @param string $property
     * @param array $input
     * @return mixed
     */
    public function convertToArray($property, array $input)
    {
        if (!$property || !$input || empty($input[$property])) {
            return $input;
        }

        return $this->processConversion($property, $input);
    }

    /**
     * @param string $property
     * @param array $input
     * @return array
     */
    abstract protected function processConversion($property, array $input);
}
