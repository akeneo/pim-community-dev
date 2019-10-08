<?php

namespace Akeneo\Tool\Component\StorageUtils\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Remove envent
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveEvent extends GenericEvent
{
    /** @var int */
    protected $subjectId;

    /**
     * @param mixed $subject
     * @param mixed $subjectId
     * @param array $arguments
     */
    public function __construct($subject, $subjectId, array $arguments = [])
    {
        parent::__construct($subject, $arguments);

        $this->subjectId = $subjectId;
    }

    /**
     * Get subject id
     *
     * @return mixed
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }
}
