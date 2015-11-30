<?php

namespace Pim\Component\Catalog\Model;

/**
 * Timestampable interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TimestampableInterface
{
    /**
     * Get created datetime
     *
     * @return \Datetime
     */
    public function getCreated();

    /**
     * Set created datetime
     *
     * @param \Datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created);

    /**
     * Get updated datetime
     *
     * @return \Datetime
     */
    public function getUpdated();

    /**
     * Set updated datetime
     *
     * @param \Datetime $updated
     *
     * @return TimestampableInterface
     */
    public function setUpdated($updated);
}
