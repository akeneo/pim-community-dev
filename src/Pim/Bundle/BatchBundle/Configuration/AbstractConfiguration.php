<?php

namespace Pim\Bundle\BatchBundle\Configuration;

use JMS\Serializer\Annotation\Exclude;

/**
 * Abstract Configuration
 *
 *
 */
abstract class AbstractConfiguration implements ConfigurationInterface, EditableConfigurationInterface
{
    /**
     * @Exclude
     * @var integer
     */
    protected $id;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
