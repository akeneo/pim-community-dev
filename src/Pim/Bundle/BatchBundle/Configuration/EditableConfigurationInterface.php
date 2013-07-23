<?php

namespace Pim\Bundle\BatchBundle\Configuration;

/**
 * Implemented by configurable configuration classes for connector and job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EditableConfigurationInterface
{
    /**
     * Get form type service id for configuration
     *
     * @return string
     */
    public function getFormTypeServiceId();
}
