<?php

namespace Oro\Bundle\EntityConfigBundle\Audit;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigLogDiff;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigLog;
use Oro\Bundle\EntityConfigBundle\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

class AuditManager
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var ServiceProxy
     */
    protected $security;

    /**
     * @param ConfigManager $configManager
     * @param ServiceProxy  $security
     */
    public function __construct(ConfigManager $configManager, ServiceProxy $security)
    {
        $this->configManager = $configManager;
        $this->security      = $security;
    }

    /**
     * Log all changed Config
     */
    public function log()
    {
        if (!$this->getUser()) {
            return;
        }

        $log = new ConfigLog();
        $log->setUser($this->getUser());

        $this->configManager->em()->persist($log);
        foreach (array_merge($this->configManager->getUpdatedEntityConfig(), $this->configManager->getUpdatedFieldConfig()) as $config) {
            $this->logConfig($config, $log);
        }

        if ($log->getDiffs()->count()) {
            $this->configManager->em()->persist($log);
        }
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigLog       $log
     */
    protected function logConfig(ConfigInterface $config, ConfigLog $log)
    {
        $changes = $this->configManager->getConfigChangeSet($config);

        $configContainer = $this->configManager->getProvider($config->getScope())->getConfigContainer();
        if ($config instanceof FieldConfigInterface) {
            $internalValues = $configContainer->getFieldInternalValues();
        } else {
            $internalValues = $configContainer->getEntityInternalValues();
        }

        $changes = array_diff_key($changes,$internalValues);

        if (!count($changes)) {
            return;
        }

        $diff = new ConfigLogDiff();
        $diff->setScope($config->getScope());
        $diff->setDiff($changes);
        $diff->setClassName($config->getClassName());

        if ($config instanceof FieldConfigInterface) {
            $diff->setFieldName($config->getCode());
        }

        $log->addDiff($diff);
    }

    /**
     * @return UserInterface
     */
    protected function getUser()
    {
        if (!$this->security->getService()->getToken() || !$this->security->getService()->getToken()->getUser()) {
            return false;
        }

        return $this->security->getService()->getToken()->getUser();
    }
}
