<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_config_log_diff")
 * @ORM\Entity
 */
class ConfigLogDiff
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigLogDiff';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigLog
     *
     * @ORM\ManyToOne(targetEntity="ConfigLog", inversedBy="diffs", cascade={"persist"})
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $log;

    /**
     * @var string
     * @ORM\Column(name="class_name", type="string", length=100)
     */
    protected $className;

    /**
     * @var string
     * @ORM\Column(name="field_name", type="string", length=100, nullable=true)
     */
    protected $fieldName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $scope;


    /**
     * @var array
     * @ORM\Column(type="text")
     */
    protected $diff;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array[] $configs
     * @return $this
     */
    public function setDiff(array $configs)
    {
        $this->diff = serialize($configs);

        return $this;
    }

    /**
     * @return array[]
     */
    public function getDiff()
    {
        return unserialize($this->diff);
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param ConfigLog $log
     * @return $this
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * @return ConfigLog
     */
    public function getLog()
    {
        return $this->log;
    }
}
