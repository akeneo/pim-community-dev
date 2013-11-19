<?php

namespace Oro\Bundle\WorkflowBundle\Model\ConfigurationPass;

interface ConfigurationPassInterface
{
    /**
     * Pass through configuration data, processes it and returns modified data
     *
     * @param array $configuration
     * @return array
     */
    public function passConfiguration(array $configuration);
}
