<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\ResourceInterface;
use Pim\Component\Resource\ResourceSetInterface;

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
     * @return string
     */
    public function getSubjectClass();

    /**
     * @return ResourceInterface|ResourceSetInterface
     */
    public function getSubject();

    /**
     * @param ResourceInterface|ResourceSetInterface $subject
     *
     * @return ResourceEventInterface
     *
     * @throws \InvalidArgumentException if the subject is not a ResourceInterface or aResourceSetInterface
     */
    public function setSubject($subject);
}
