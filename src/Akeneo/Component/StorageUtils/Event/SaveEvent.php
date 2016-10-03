<?php

namespace Akeneo\Component\StorageUtils\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Remove envent
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Grégory Planchat <gregory@kiboko.fr>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveEvent extends GenericEvent implements StorageEventInterface
{
    /**
     * @param mixed $subject
     * @param array $arguments
     */
    public function __construct($subject, array $arguments = [])
    {
        parent::__construct($subject, $arguments);
    }

    /**
     * @return bool
     */
    public function isBulk()
    {
        return false;
    }
}
