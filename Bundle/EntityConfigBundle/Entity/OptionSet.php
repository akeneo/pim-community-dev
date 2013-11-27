<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oro_entity_config_optionset")
 * @ORM\HasLifecycleCallbacks
 */
class OptionSet
{
    const ENTITY_NAME = 'OroEntityConfigBundle:OptionSet';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var FieldConfigModel
     * @ORM\ManyToOne(targetEntity="FieldConfigModel", inversedBy="options", cascade={"all"})
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $field;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $label;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $priority;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $is_default;

    /**
     * @var OptionSetRelation[]|PersistentCollection
     * @ORM\OneToMany(targetEntity="OptionSetRelation", mappedBy="option", cascade={"all"})
     */
    protected $relation;

    /**
     * @return mixed
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param mixed $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->label;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param boolean $default
     * @return $this
     */
    public function setIsDefault($default)
    {
        $this->is_default = $default;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * @param $id
     * @param $priority
     * @param $label
     * @param $default
     */
    public function setData($id, $priority, $label, $default)
    {
        $this
            ->setId($id)
            ->setPriority($priority)
            ->setLabel($label)
            ->setIsDefault($default);
    }
}
