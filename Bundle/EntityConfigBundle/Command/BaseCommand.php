<?php

namespace Oro\Bundle\EntityConfigBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this->getContainer()->get('oro_entity_config.config_manager');
    }
}
