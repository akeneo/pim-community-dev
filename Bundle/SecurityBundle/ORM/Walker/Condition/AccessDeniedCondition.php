<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker\Condition;

class AccessDeniedCondition
{
    /**
     * @var string
     */
    protected $entityAlias;

    /**
     * @var string
     */
    protected $targetClass;

    /**
     * @var int
     */
    protected $fromKey;

    /**
     * @var int
     */
    protected $joinKey;

    /**
     * @param string $entityAlias
     * @param string $targetClass
     */
    public function __construct($entityAlias, $targetClass = '')
    {
        $this->entityAlias = $entityAlias;
        $this->targetClass = $targetClass;
    }

    /**
     * @param string $entityAlias
     */
    public function setEntityAlias($entityAlias)
    {
        $this->entityAlias = $entityAlias;
    }

    /**
     * @return string
     */
    public function getEntityAlias()
    {
        return $this->entityAlias;
    }

    /**
     * @param int $fromKey
     */
    public function setFromKey($fromKey)
    {
        $this->fromKey = $fromKey;
    }

    /**
     * @return int
     */
    public function getFromKey()
    {
        return $this->fromKey;
    }

    /**
     * @param int $joinKey
     */
    public function setJoinKey($joinKey)
    {
        $this->joinKey = $joinKey;
    }

    /**
     * @return int
     */
    public function getJoinKey()
    {
        return $this->joinKey;
    }

    /**
     * @param string $targetClass
     */
    public function setTargetClass($targetClass)
    {
        $this->targetClass = $targetClass;
    }

    /**
     * @return string
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }
}
