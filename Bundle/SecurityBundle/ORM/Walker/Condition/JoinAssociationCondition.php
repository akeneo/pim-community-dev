<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker\Condition;


class JoinAssociationCondition extends JoinAclCondition
{
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var []
     */
    protected $joinConditions;

    /**
     * @param $entityAlias
     * @param $entityField
     * @param $value
     * @param $entityClass
     * @param $joinConditions
     */
    public function __construct($entityAlias, $entityField = null, $value = null, $entityClass = null, $joinConditions = null)
    {
        $this->entityClass = $entityClass;
        $this->joinConditions = $joinConditions;

        parent::__construct($entityAlias, $entityField, $value);
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param array $joinConditions
     */
    public function setJoinConditions($joinConditions)
    {
        $this->joinConditions = $joinConditions;
    }

    /**
     * @return array
     */
    public function getJoinConditions()
    {
        return $this->joinConditions;
    }
}
