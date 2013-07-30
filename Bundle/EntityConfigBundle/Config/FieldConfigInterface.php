<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

interface FieldConfigInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getCode();
}
