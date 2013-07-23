<?php
namespace Pim\Bundle\BatchBundle\Configuration;

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
