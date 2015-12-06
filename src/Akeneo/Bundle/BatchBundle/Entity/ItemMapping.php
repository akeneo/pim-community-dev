<?php

namespace Akeneo\Bundle\BatchBundle\Entity;

use Akeneo\Bundle\BatchBundle\Transform\Mapping\ItemMapping as ItemMappingModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity ItemMapping
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="akeneo_batch_mapping_item")
 * @ORM\Entity
 *
 * @deprecated will be removed in 1.6
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
     * @param \Akeneo\Bundle\BatchBundle\Entity\FieldMapping $fields
     *
     * @return ItemMapping
     */
    public function addField(\Akeneo\Bundle\BatchBundle\Entity\FieldMapping $fields)
    {
        $this->fields[] = $fields;

        return $this;
    }

    /**
     * Remove fields
     *
     * @param \Akeneo\Bundle\BatchBundle\Entity\FieldMapping $fields
     */
    public function removeField(\Akeneo\Bundle\BatchBundle\Entity\FieldMapping $fields)
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
