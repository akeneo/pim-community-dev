<?php

namespace Akeneo\Bundle\BatchBundle\Transform\Mapping;

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
    protected $identifier;

    /**
     * Set source
     *
     * @param string $source
     *
     * @return \Akeneo\Bundle\BatchBundle\Mapping\FieldMapping
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
     * @return \Akeneo\Bundle\BatchBundle\Mapping\FieldMapping
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
     * @return \Akeneo\Bundle\BatchBundle\Mapping\FieldMapping
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
}
