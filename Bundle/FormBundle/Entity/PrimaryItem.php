<?php

namespace Oro\Bundle\FormBundle\Entity;

interface PrimaryItem
{
    /**
     * Is item primary
     *
     * @return bool
     */
    public function isPrimary();

    /**
     * Set item primary
     *
     * @param bool $value
     */
    public function setPrimary($value);
}
