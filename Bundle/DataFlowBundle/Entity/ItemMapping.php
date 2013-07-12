<?php
namespace Oro\Bundle\DataFlowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataFlowBundle\Transform\Mapping\ItemMapping as ItemMappingModel;

/**
 * Entity ItemMapping
 *
 *
 * @ORM\Table(name="oro_dataflow_mapping_item")
 * @ORM\Entity
 */
class ItemMapping extends ItemMappingModel
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var multitype
     *
     * @ORM\OneToMany(targetEntity="FieldMapping", mappedBy="item", cascade={"persist", "remove"})
     */
    protected $fields;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add fields
     *
     * @param \Oro\Bundle\DataFlowBundle\Entity\FieldMapping $fields
     *
     * @return ItemMapping
     */
    public function addField(\Oro\Bundle\DataFlowBundle\Entity\FieldMapping $fields)
    {
        $this->fields[] = $fields;

        return $this;
    }

    /**
     * Remove fields
     *
     * @param \Oro\Bundle\DataFlowBundle\Entity\FieldMapping $fields
     */
    public function removeField(\Oro\Bundle\DataFlowBundle\Entity\FieldMapping $fields)
    {
        $this->fields->removeElement($fields);
    }

    /**
     * Get fields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFields()
    {
        return $this->fields;
    }
}
