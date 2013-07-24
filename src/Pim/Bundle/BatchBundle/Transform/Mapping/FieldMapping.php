<?php
namespace Pim\Bundle\BatchBundle\Transform\Mapping;

/**
 * Field mapping
 *
 *
 */
class FieldMapping
{

    /**
     * Source field name
     * @var string
     */
    protected $source;

    /**
     * Destination field name
     * @var string
     */
    protected $destination;

    /**
     * Predicate to know if field is an identifier
     * @var boolean
     */
    protected $isIdentifier;

    /**
     * Set source
     *
     * @param string $source
     *
     * @return \Pim\Bundle\BatchBundle\Mapping\FieldMapping
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
     * @param param $destination
     *
     * @return \Pim\Bundle\BatchBundle\Mapping\FieldMapping
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
     * @return \Pim\Bundle\BatchBundle\Mapping\FieldMapping
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
}
