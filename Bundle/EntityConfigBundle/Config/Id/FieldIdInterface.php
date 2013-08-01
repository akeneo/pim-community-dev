<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

interface FieldIdInterface extends IdInterface
{
    /**
     * @return string
     */
    public function getFieldName();

    /**
     * @return string
     */
    public function getFieldType();
}
