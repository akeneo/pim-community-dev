<?php

namespace Pim\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\BatchBundle\Transform\Mapping\ItemMapping as ItemMappingModel;

/**
 * Entity ItemMapping
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="pim_batch_mapping_item")
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
     * @param \Pim\Bundle\BatchBundle\Entity\FieldMapping $fields
     *
     * @return ItemMapping
     */
    public function addField(\Pim\Bundle\BatchBundle\Entity\FieldMapping $fields)
    {
        $this->fields[] = $fields;

        return $this;
    }

    /**
     * Remove fields
     *
     * @param \Pim\Bundle\BatchBundle\Entity\FieldMapping $fields
     */
    public function removeField(\Pim\Bundle\BatchBundle\Entity\FieldMapping $fields)
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
