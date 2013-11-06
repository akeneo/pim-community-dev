<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible entity interface, allow to define a flexible entity without extends abstract class
 */
interface FlexibleInterface
{
    /**
     * Add value
     *
     * @param FlexibleValueInterface $value
     *
     * @return FlexibleInterface
     */
    public function addValue(FlexibleValueInterface $value);

    /**
     * Remove value
     *
     * @param FlexibleValueInterface $value
     */
    public function removeValue(FlexibleValueInterface $value);

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getValues();

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     *
     * @return FlexibleValueInterface
     */
    public function getValue($attributeCode);
}
