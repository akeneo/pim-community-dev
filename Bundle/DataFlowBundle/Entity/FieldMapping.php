<?php
namespace Oro\Bundle\DataFlowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataFlowBundle\Transform\Mapping\FieldMapping as FieldMappingModel;

/**
 * Entity FieldMapping
 *
 *
 * @ORM\Table(name="oro_dataflow_mapping_field")
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
     * @ORM\Column(name="is_identifier", type="boolean")
     */
    protected $isIdentifier;

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
        $this->isIdentifier = false;
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
     * Set isIdentifier
     *
     * @param boolean $isIdentifier
     *
     * @return FieldMapping
     */
    public function setIsIdentifier($isIdentifier)
    {
        $this->isIdentifier = $isIdentifier;

        return $this;
    }

    /**
     * Get isIdentifier
     *
     * @return boolean
     */
    public function getIsIdentifier()
    {
        return $this->isIdentifier;
    }

    /**
     * Set item
     *
     * @param \Oro\Bundle\DataFlowBundle\Entity\ItemMapping $item
     *
     * @return FieldMapping
     */
    public function setItem(\Oro\Bundle\DataFlowBundle\Entity\ItemMapping $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \Oro\Bundle\DataFlowBundle\Entity\ItemMapping
     */
    public function getItem()
    {
        return $this->item;
    }
}
