<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

/**
 * Configuration interface
 *
 *
 */
interface ConfigurationInterface
{
    /**
     * Get configuration id
     *
     * @return integer
     */
    public function getId();

    /**
     * @param integer $id
     *
     * @return ConfigurationInterface
     */
    public function setId($id);
}
