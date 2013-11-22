<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker\Condition;

class SubRequestAclConditionStorage extends AclConditionStorage
{
    /**
     * @var int
     */
    protected $factorId;

    /**
     * @return bool
     */
    public function isEmpty()
    {
        if (!empty($this->whereConditions)) {
            return false;
        }
        if (!empty($this->joinConditions)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $factorId
     */
    public function setFactorId($factorId)
    {
        $this->factorId = $factorId;
    }

    /**
     * @return int
     */
    public function getFactorId()
    {
        return $this->factorId;
    }
}
