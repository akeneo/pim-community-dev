<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oro_entity_config_optionset_relation")
 * @ORM\HasLifecycleCallbacks
 */
class OptionSetRelation
{
    const ENTITY_NAME = 'OroEntityConfigBundle:OptionSetRelation';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var EntityConfigModel
     * @ORM\ManyToOne(targetEntity="EntityConfigModel", inversedBy="fields", cascade={"persist"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     * })
     */
    protected $entity;

    /**
     * @var FieldConfigModel
     * @ORM\ManyToOne(targetEntity="FieldConfigModel", inversedBy="options", cascade={"persist"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    protected $field;

    /**
     * @var OptionSet
     * @ORM\ManyToOne(targetEntity="OptionSet", inversedBy="options", cascade={"persist"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    protected $option;

    /**
     * @param \Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return \Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param \Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return \Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel
     */
    public function getField()
    {
        return $this->field;
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Oro\Bundle\EntityConfigBundle\Entity\OptionSet $option
     * @return $this
     */
    public function setOption($option)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * @return \Oro\Bundle\EntityConfigBundle\Entity\OptionSet
     */
    public function getOption()
    {
        return $this->option;
    }
}
