<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;

/**
 * @ORM\Table(name="oro_entity_config_field")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class FieldConfigModel extends AbstractConfigModel
{
    const ENTITY_NAME = 'OroEntityConfigBundle:FieldConfigModel';

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var EntityConfigModel
     * @ORM\ManyToOne(targetEntity="EntityConfigModel", inversedBy="fields", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var ConfigModelValue[]|PersistentCollection
     * @ORM\OneToMany(targetEntity="ConfigModelValue", mappedBy="field", cascade={"all"})
     */
    protected $values;

    /**
     * @var OptionSet[]|PersistentCollection
     * @ORM\OneToMany(targetEntity="OptionSet", mappedBy="field", cascade={"all"})
     */
    protected $options;

    /**
     * @var string
     * @ORM\Column(name="field_name", type="string", length=255)
     */
    protected $fieldName;

    /**
     * @var string
     * @ORM\Column(type="string", length=60, nullable=false)
     */
    protected $type;

    public function __construct($fieldName = null, $type = null)
    {
        $this->type      = $type;
        $this->mode      = ConfigModelManager::MODE_DEFAULT;
        $this->values    = new ArrayCollection;
        $this->fieldName = $fieldName;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
}
