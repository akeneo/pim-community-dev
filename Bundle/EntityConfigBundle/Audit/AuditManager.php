<?php

namespace Oro\Bundle\EntityConfigBundle\Audit;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigLogDiff;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigLog;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

/**
 * Audit config data
 */
class AuditManager
{
    /**
     * @var ServiceLink
     */
    protected $configManagerLink;

    /**
     * @var ServiceLink
     */
    protected $securityLink;

    /**
     * @param ServiceLink $configManagerLink
     * @param ServiceLink $securityLink
     */
    public function __construct(ServiceLink $configManagerLink, ServiceLink $securityLink)
    {
        $this->configManagerLink = $configManagerLink;
        $this->securityLink      = $securityLink;
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

        foreach ($this->getConfigManager()->getUpdateConfig() as $config) {
            $this->logConfig($config, $log);
        }

        if ($log->getDiffs()->count()) {
            $this->getConfigManager()->getEntityManager()->persist($log);
        }
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigLog       $log
     */
    protected function logConfig(ConfigInterface $config, ConfigLog $log)
    {
        $changes = $this->getConfigManager()->getConfigChangeSet($config);

        $configId        = $config->getId();
        $configContainer = $this->getConfigManager()->getProvider($configId->getScope())->getPropertyConfig();
        $internalValues  = $configContainer->getNotAuditableValues($configId);

        $changes = array_diff_key($changes, $internalValues);
        if (!count($changes)) {
            return;
        }

        $diff = new ConfigLogDiff();
        $diff->setScope($configId->getScope());
        $diff->setDiff($changes);
        $diff->setClassName($configId->getClassName());

        if ($configId instanceof FieldConfigIdInterface) {
            $diff->setFieldName($configId->getFieldName());
        }

        $log->addDiff($diff);
    }

    /**
     * @return UserInterface
     */
    protected function getUser()
    {
        /** @var SecurityContext $security */
        $security = $this->securityLink->getService();
        if (!$security->getToken() || !$security->getToken()->getUser()) {
            return false;
        }

        return $security->getToken()->getUser();
    }

    /**
     * @return ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->configManagerLink->getService();
    }
}
