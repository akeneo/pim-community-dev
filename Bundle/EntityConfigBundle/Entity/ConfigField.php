<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Table(name="oro_config_field")
 * @ORM\Entity
 */
class ConfigField
{
    const ENTITY_NAME = 'OroEntityConfigBundle:ConfigField';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ConfigEntity
     *
     * @ORM\ManyToOne(targetEntity="ConfigEntity", inversedBy="fields")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var ConfigValue[]|PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="ConfigValue", mappedBy="field", cascade={"all"})
     */
    protected $values;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $code;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param ConfigValue[] $values
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
        $value->setField($this);
        $this->values->add($value);

        return $this;
    }

    /**
     * @return ConfigValue[]|PersistentCollection
     */
    public function getValues()
    {
        return $this->values;
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
}
