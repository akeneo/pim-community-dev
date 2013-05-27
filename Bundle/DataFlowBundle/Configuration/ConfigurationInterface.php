<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

/**
 * Configuration interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
