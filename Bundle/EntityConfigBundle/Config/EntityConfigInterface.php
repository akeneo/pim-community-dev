<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

interface EntityConfigInterface
{
    /**
     * @param  callable      $filter
     * @return FieldConfig[]
     */
    public function getFields(\Closure $filter = null);
}
