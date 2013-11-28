<?php

namespace Oro\Bundle\EntityConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\EntityConfigBundle\Entity\Repository\OptionSetRelationRepository")
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
     * @var FieldConfigModel
     * @ORM\ManyToOne(targetEntity="FieldConfigModel", inversedBy="options")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    protected $field;

    /**
     * @var OptionSet
     * @ORM\ManyToOne(targetEntity="OptionSet", inversedBy="relation", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $option;

    /**
     * @ORM\Column(type="integer")
     */
    protected $entity_id;


    /**
     * @param int $entity_id
     * @return $this
     */
    public function setEntityId($entity_id)
    {
        $this->entity_id = $entity_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entity_id;
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

    /**
     * @param $id
     * @param $entity_id
     * @param $field
     * @param $option
     * @internal param $field
     */
    public function setData($id, $entity_id, $field, $option)
    {
        $this
            ->setId($id)
            ->setEntityId($entity_id)
            ->setField($field)
            ->setOption($option);
    }
}
