<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_config_value")
 * @ORM\Entity
 */
class ConfigValue
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigValue';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigEntity
     *
     * @ORM\ManyToOne(targetEntity="ConfigEntity", inversedBy="values")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var ConfigField
     *
     * @ORM\ManyToOne(targetEntity="ConfigField", inversedBy="values")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id")
     * })
     */
    protected $field;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $scope;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    public function __construct($code = null, $scope = null, $value = null)
    {
        $this->code  = $code;
        $this->scope = $scope;
        $this->value = $value;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return ConfigValue
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $scope
     *
     * @return ConfigValue
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
     *
     * @param string $data
     *
     * @return ConfigValue
     */
    public function setValue($data)
    {
        $this->value = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param ConfigEntity $entity
     *
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return ConfigEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param ConfigField $field
     *
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return ConfigField
     */
    public function getField()
    {
        return $this->field;
    }

    public function toArray()
    {
        return array(
            'code'  => $this->code,
            'scope' => $this->scope,
            'value' => $this->value
        );
    }
}
