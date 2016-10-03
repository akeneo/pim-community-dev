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
class BulkRemoveEvent extends GenericEvent implements SubjectAwareInterface, StorageEventInterface
{
    use SubjectIdAwareTrait;

    /**
     * @param mixed $subject
     * @param int   $subjectId
     * @param array $arguments
     */
    public function __construct($subject, $subjectId, array $arguments = [])
    {
        parent::__construct($subject, $arguments);

        $this->setSubjectId($subjectId);
    }

    /**
     * @return bool
     */
    public function isBulk()
    {
        return true;
    }
}
