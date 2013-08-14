<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;

/**
 * @ORM\Table(name="oro_config_entity")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class EntityConfigModel extends AbstractConfigModel
{
    const ENTITY_NAME = 'OroEntityConfigBundle:EntityConfigModel';

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigModelValue[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ConfigModelValue", mappedBy="entity", cascade={"all"})
     */
    protected $values;

    /**
     * @var FieldConfigModel[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="FieldConfigModel", mappedBy="entity", cascade={"all"})
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
        $this->mode      = ConfigModelManager::MODE_DEFAULT;
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
     * @param FieldConfigModel[] $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param FieldConfigModel $field
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
     * @return FieldConfigModel[]|ArrayCollection
     */
    public function getFields(\Closure $filter = null)
    {
        return $filter ? $this->fields->filter($filter) : $this->fields;
    }

    /**
     * @param $fieldName
     * @return FieldConfigModel
     */
    public function getField($fieldName)
    {
        $fields = $this->getFields(
            function (FieldConfigModel $field) use ($fieldName) {
                return $field->getFieldName() == $fieldName;
            }
        );

        return $fields->first();
    }
}
