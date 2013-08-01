<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

interface FieldConfigIdInterface extends ConfigIdInterface
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
