<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker\Condition;

class AclConditionStorage
{
    /**
     * @var AclCondition[]
     */
    protected $whereConditions;

    /**
     * @var JoinAclCondition[]
     */
    protected $joinConditions;

    /**
     * @var SubRequestAclConditionStorage|SubRequestAclConditionStorage[]
     */
    protected $subRequests;

    public function __construct(array $whereConditions, array $joinConditions)
    {
        $this->whereConditions = $whereConditions;
        $this->joinConditions = $joinConditions;
    }

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
        if ($this->subRequests) {
            return false;
        }

        return true;
    }

    /**
     * @param JoinAclCondition[] $joinConditions
     */
    public function setJoinConditions($joinConditions)
    {
        $this->joinConditions = $joinConditions;
    }

    /**
     * @return JoinAclCondition[]
     */
    public function getJoinConditions()
    {
        return $this->joinConditions;
    }

    /**
     * @param SubRequestAclConditionStorage|SubRequestAclConditionStorage[] $subRequests
     */
    public function setSubRequests($subRequests)
    {
        $this->subRequests = $subRequests;
    }

    /**
     * @param SubRequestAclConditionStorage|SubRequestAclConditionStorage[] $subRequests
     */
    public function addSubRequests($subRequests)
    {
        $this->subRequests[] = $subRequests;
    }

    /**
     * @return SubRequestAclConditionStorage|SubRequestAclConditionStorage[]
     */
    public function getSubRequests()
    {
        return $this->subRequests;
    }

    /**
     * @param AclCondition[] $whereConditions
     */
    public function setWhereConditions($whereConditions)
    {
        $this->whereConditions = $whereConditions;
    }

    /**
     * @return AclCondition[]
     */
    public function getWhereConditions()
    {
        return $this->whereConditions;
    }
}
