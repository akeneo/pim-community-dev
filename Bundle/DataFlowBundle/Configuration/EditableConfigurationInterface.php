<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

/**
 * Implemented by configurable classes as connector and job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
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
