<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Timestampable interface
 *
 *
 */
interface TimestampableInterface
{
    /**
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated();

    /**
     * Set created datetime
     *
     * @param datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created);

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated();

    /**
     * Set updated datetime
     *
     * @param datetime $updated
     *
     * @return TimestampableInterface
    */
    public function setUpdated($updated);
}
