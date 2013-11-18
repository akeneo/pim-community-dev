<?php

namespace Oro\Bundle\SecurityBundle\ORM\Walker\Condition;


class AclCondition
{
    /**
     * @var string
     */
    protected $entityAlias;

    /**
     * @var string
     */
    protected $entityField;

    /**
     * @var int[]
     */
    protected $value;

    /**
     * @param $entityAlias
     * @param null $entityField
     * @param null $value
     */
    public function __construct($entityAlias, $entityField = null, $value = null)
    {
        $this->entityAlias = $entityAlias;
        $this->entityField = $entityField;
        $this->value = $value;
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
     * @param string $entityField
     */
    public function setEntityField($entityField)
    {
        $this->entityField = $entityField;
    }

    /**
     * @return string
     */
    public function getEntityField()
    {
        return $this->entityField;
    }

    /**
     * @param int[] $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int[]
     */
    public function getValue()
    {
        return $this->value;
    }
}
