<?php

namespace Oro\Bundle\EmailBundle\Provider;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class VariablesProvider
{
    /** @var ConfigProvider  */
    protected $configProvider;

    /** @var SecurityContextInterface  */
    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext, ConfigProvider $provider)
    {
        $this->securityContext = $securityContext;
        $this->configProvider = $provider;
    }

    /**
     * Return available in template variables
     *
     * @param string $entityName
     * @return array
     */
    public function getTemplateVariables($entityName)
    {
        $userClassName = $this->getUser() ? get_class($this->getUser()) : false;
        $allowedData = array(
            'entity' => array(),
            'user'   => array()
        );

        foreach ($this->configProvider->getConfigIds() as $entityConfigId) {
            // export variables of asked entity and current user entity class
            $className = $entityConfigId->getClassName();
            if ($className == $entityName || $className == $userClassName) {
                $fields = $this->configProvider->filter(
                    function (ConfigInterface $config) {
                        return $config->is('available_in_template');
                    },
                    $className
                );

                $fields = array_values(
                    array_map(
                        function (ConfigInterface $field) {
                            return $field->getConfigId()->getFieldName();
                        },
                        $fields
                    )
                );

                switch ($className) {
                    case $entityName:
                        $allowedData['entity'] = $fields;
                        break;
                    case $userClassName:
                        $allowedData['user'] = $fields;
                        break;
                }

                if ($entityName == $userClassName) {
                    $allowedData['user'] = $allowedData['entity'];
                }
            }
        }

        return $allowedData;
    }

    /**
     * Return current user
     *
     * @return UserInterface|bool
     */
    private function getUser()
    {
        return $this->securityContext->getToken() && !is_string($this->securityContext->getToken()->getUser())
            ? $this->securityContext->getToken()->getUser() : false;
    }
}
