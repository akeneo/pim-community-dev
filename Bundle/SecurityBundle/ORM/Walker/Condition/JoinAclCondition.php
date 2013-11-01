<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker\Condition;


class JoinAclCondition extends AclCondition
{
    /**
     * @var int
     */
    protected $fromKey;

    /**
     * @var int
     */
    protected $joinKey;

    /**
     * @return int
     */
    public function getFromKey()
    {
        return $this->fromKey;
    }

    /**
     * @return int
     */
    public function getJoinKey()
    {
        return $this->joinKey;
    }

    /**
     * @param int $fromKey
     */
    public function setFromKey($fromKey)
    {
        $this->fromKey = $fromKey;
    }

    /**
     * @param int $joinKey
     */
    public function setJoinKey($joinKey)
    {
        $this->joinKey = $joinKey;
    }
}
