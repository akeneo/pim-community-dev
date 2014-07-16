<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Exception;

/**
 * Exception raises when try to remove an entity linked to published product
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductConsistencyException extends \Exception
{
    /** @var boolean */
    protected $needsRedirection;

    /**
     * @param string     $message
     * @param integer    $code
     * @param \Exception $previous
     * @param boolean    $needsRedirection
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null, $needsRedirection = false)
    {
        parent::__construct($message, $code, $previous);

        $this->needsRedirection = $needsRedirection;
    }

    /**
     * Predicate to know if this exception needs redirection or not
     *
     * @return boolean
     */
    public function needsRedirection()
    {
        return $this->needsRedirection;
    }
}
