<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_config_value")
 * @ORM\Entity
 */
class ConfigModelValue
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigModelValue';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var EntityConfigModel
     * @ORM\ManyToOne(targetEntity="EntityConfigModel", inversedBy="values")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var FieldConfigModel
     * @ORM\ManyToOne(targetEntity="FieldConfigModel", inversedBy="values")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id")
     * })
     */
    protected $field;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $scope;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $serializable;

    public function __construct($code = null, $scope = null, $value = null, $serializable = false)
    {
        $this->code         = $code;
        $this->scope        = $scope;
        $this->serializable = $serializable;

        $this->setValue($value);
    }

    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     * @param string $code
     * @return ConfigModelValue
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $scope
     * @return ConfigModelValue
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
     * Set data
     * @param string $value
     * @return ConfigModelValue
     */
    public function setValue($value)
    {
        $this->value = $this->serializable ? serialize($value) : $value;

        return $this;
    }

    /**
     * Get data
     * @return string
     */
    public function getValue()
    {
        return $this->serializable ? unserialize($this->value) : $this->value;
    }

    /**
     * @param EntityConfigModel $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return EntityConfigModel
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param FieldConfigModel $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return FieldConfigModel
     */
    public function getField()
    {
        return $this->field;
    }

    public function toArray()
    {
        return array(
            'code'         => $this->code,
            'scope'        => $this->scope,
            'value'        => $this->serializable ? unserialize($this->value) : $this->value,
            'serializable' => $this->serializable
        );
    }

    /**
     * @param boolean $serializable
     * @return $this
     */
    public function setSerializable($serializable)
    {
        $this->serializable = $serializable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSerializable()
    {
        return $this->serializable;
    }
}
