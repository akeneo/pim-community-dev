<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\Model\ResourceInterface;
use Pim\Component\Resource\Model\ResourceSetInterface;

/**
 * Default resource event interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceEventInterface
{
    /**
     * Get the class of the subject related to this event.
     *
     * @return string
     */
    public function getSubjectClass();

    /**
     * Get the subject of this event.

     * @return \Pim\Component\Resource\Model\ResourceInterface|ResourceSetInterface
     */
    public function getSubject();

    /**
     * Set the subject of this event.
     *
     * @param \Pim\Component\Resource\Model\ResourceInterface|ResourceSetInterface $subject
     *
     * @return ResourceEventInterface
     *
     * @throws \InvalidArgumentException if the subject is not a ResourceInterface or aResourceSetInterface
     */
    public function setSubject($subject);
}
