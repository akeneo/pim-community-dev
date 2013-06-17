<?php

namespace Oro\Bundle\EntityConfigBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\EntityConfigBundle\ConfigBackendManager;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return ConfigBackendManager
     */
    public function getConfigManager()
    {
        return $this->getContainer()->get('oro_entity_config.config_backend_manager');
    }
}
