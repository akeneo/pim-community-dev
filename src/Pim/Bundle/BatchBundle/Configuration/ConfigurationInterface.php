<?php

namespace Pim\Bundle\BatchBundle\Configuration;

/**
 * Configuration interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
