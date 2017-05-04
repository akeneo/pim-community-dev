<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Exception;

/**
 * Exception raises when try to remove an entity linked to published product
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PublishedProductConsistencyException extends \Exception
{
    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(
        $message,
        $code = 409,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
