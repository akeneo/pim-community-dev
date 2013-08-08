<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_config_entity")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class ConfigEntity extends AbstractConfig
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigEntity';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigValue[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ConfigValue", mappedBy="entity", cascade={"all"})
     */
    protected $values;

    /**
     * @var ConfigField[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ConfigField", mappedBy="entity", cascade={"all"})
     */
    protected $fields;

    /**
     * @var string
     * @ORM\Column(name="class_name", type="string", length=255)
     */
    protected $className;

    public function __construct($className = null)
    {
        $this->className = $className;
        $this->fields    = new ArrayCollection();
        $this->values    = new ArrayCollection();
        $this->mode      = self::MODE_VIEW_DEFAULT;
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
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param ConfigField $field
     * @return $this
     */
    public function addField($field)
    {
        $field->setEntity($this);
        $this->fields->add($field);

        return $this;
    }

    /**
     * @param  callable $filter
     * @return ConfigField[]|ArrayCollection
     */
    public function getFields(\Closure $filter = null)
    {
        return $filter ? $this->fields->filter($filter) : $this->fields;
    }

    /**
     * @param $code
     * @return ConfigField
     */
    public function getField($code)
    {
        $fields = $this->getFields(function (ConfigField $field) use ($code) {
            return $field->getCode() == $code;
        });

        return $fields->first();
    }
}
