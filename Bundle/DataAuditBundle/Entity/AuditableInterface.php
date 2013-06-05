<?php

namespace Oro\Bundle\DataAuditBundle\Entity;

/**
 * AuditableInterface indicates, that entity provides structured information about it's versioned collections
 */
interface AuditableInterface
{
    /**
     * Generate array of entity collectinos in format
     *  array(
     *      "fieldName" => "stringifiedValue"
     *      ...
     *  )
     */
    public function setAuditData();
}
