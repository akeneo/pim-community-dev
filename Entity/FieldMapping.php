<?php

namespace Akeneo\Bundle\BatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Akeneo\Bundle\BatchBundle\Transform\Mapping\FieldMapping as FieldMappingModel;

/**
 * Entity field mapping
 *
 *
 * @ORM\Table(name="akeneo_batch_mapping_field")
 * @ORM\Entity
 */
class FieldMapping extends FieldMappingModel
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
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     */
    protected $source;

    /**
     * @var string
     *
     * @ORM\Column(name="destination", type="string", length=255)
     */
    protected $destination;

    /**
     * @var boolean
     *
     * @ORM\Column(name="identifier", type="boolean")
     */
    protected $identifier;

    /**
     * @var ItemMapping
     *
     * @ORM\ManyToOne(targetEntity="ItemMapping", inversedBy="fields")
     */
    protected $item;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->identifier = false;
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
     * Set source
     *
     * @param string $source
     *
     * @return FieldMapping
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set destination
     *
     * @param string $destination
     *
     * @return FieldMapping
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set identifier
     *
     * @param boolean $identifier
     *
     * @return FieldMapping
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return boolean
     */
    public function isIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set item
     *
     * @param \Akeneo\Bundle\BatchBundle\Entity\ItemMapping $item
     *
     * @return FieldMapping
     */
    public function setItem(\Akeneo\Bundle\BatchBundle\Entity\ItemMapping $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Akeneo\Bundle\BatchBundle\Entity\ItemMapping
     */
    public function getItem()
    {
        return $this->item;
    }
}
