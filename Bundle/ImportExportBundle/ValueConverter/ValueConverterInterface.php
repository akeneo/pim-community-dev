<?php

namespace Oro\Bundle\ImportExportBundle\ValueConverter;

interface ValueConverterInterface
{
    /**
     * @param mixed $input
     * @return mixed
     */
    public function convertToString($input = null);
}
