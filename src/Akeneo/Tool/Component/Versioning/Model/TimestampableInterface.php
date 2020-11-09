<?php

namespace Akeneo\Tool\Component\Versioning\Model;

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
     */
    public function getCreated(): \DateTime;

    /**
     * Set created datetime
     *
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);

    /**
     * Get updated datetime
     */
    public function getUpdated(): \DateTime;

    /**
     * Set updated datetime
     *
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);
}
