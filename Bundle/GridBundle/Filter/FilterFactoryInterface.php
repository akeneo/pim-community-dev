<?php

namespace Oro\Bundle\GridBundle\Filter;

interface FilterFactoryInterface
{
    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @return FilterInterface
     */
    public function create($name, $type, array $options = array());
}
