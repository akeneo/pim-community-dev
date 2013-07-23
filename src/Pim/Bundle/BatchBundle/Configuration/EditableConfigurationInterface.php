<?php
namespace Pim\Bundle\BatchBundle\Configuration;

/**
 * Implemented by configurable classes as connector and job
 *
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
