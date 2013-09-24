<?php

namespace Oro\Bundle\GridBundle\Property;

interface FormatterInterface
{
    /**
     * Format value for output
     * Used in case when datagrid should return localized data
     * Use service tag oro_grid.property.formatter for registering formatters, tag property type will be used
     * for formatter choosing
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function format($value);
}
