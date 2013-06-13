<?php

namespace Oro\Bundle\ImportExportBundle\ItemConverter;

interface ItemConverterInterface
{
    /**
     * @param string $property
     * @param array $input
     * @return mixed
     */
    public function convertToArray($property, array $input);
}
