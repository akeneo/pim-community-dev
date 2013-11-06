<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible value interface, allow to define a flexible value without extends abstract class
 */
interface FlexibleValueInterface
{
    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute();

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();
}
