<?php

namespace Akeneo\Component\StorageUtils\Event;

/**
 * Remove envent
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Grégory Planchat <gregory@kiboko.fr>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SubjectAwareInterface
{
    /**
     * Getter for subject property.
     *
     * @return mixed $subject The observer subject.
     */
    public function getSubject();

    /**
     * Get subject id
     *
     * @return int
     */
    public function getSubjectId();
}
