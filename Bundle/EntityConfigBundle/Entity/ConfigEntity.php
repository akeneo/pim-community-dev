<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_config_entity")
 * @ORM\Entity
 */
class ConfigEntity
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigEntity';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigValue[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ConfigValue", mappedBy="entity", cascade={"all"})
     */
    protected $values;

    /**
     * @var ConfigField[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ConfigField", mappedBy="entity", cascade={"all"})
     */
    protected $fields;

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false)
     */
    protected $className;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->values = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $className
     *
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
     * @param ConfigField[] $fields
     *
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param ConfigField $field
     *
     * @return $this
     */
    public function addFiled($field)
    {
        $field->setEntity($this);
        $this->fields->add($field);

        return $this;
    }

    /**
     * @return ConfigField[]|ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param ConfigValue[] $values
     *
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param ConfigValue $value
     *
     * @return $this
     */
    public function addValue($value)
    {
        $value->setEntity($this);
        $this->values->add($value);

        return $this;
    }

    /**
     * @return ConfigValue[]|ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }
}
