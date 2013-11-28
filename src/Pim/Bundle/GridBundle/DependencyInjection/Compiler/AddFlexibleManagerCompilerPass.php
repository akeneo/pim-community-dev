<?php

namespace Pim\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AbstractDatagridManagerCompilerPass;

class AddFlexibleManagerCompilerPass extends AbstractDatagridManagerCompilerPass
{
    const FLEXIBLE_MANAGER_FACTORY_KEY    = 'pim_flexibleentity.registry';
    const FLEXIBLE_MANAGER_FACTORY_METHOD = 'getManager';
    const FLEXIBLE_MANAGER_CLASS          = 'Pim\\Bundle\\FlexibleEntityBundle\\Manager\\FlexibleManager';
    const FLEXIBLE_MANAGER_ATTRIBUTE      = 'flexible_manager';
    const FLEXIBLE_ATTRIBUTE              = 'flexible';
    const ENTITY_NAME_ATTRIBUTE           = 'entity_name';

    /**
     * {@inheritDoc}
     */
    public function processDatagrid()
    {
        $managerSetter = 'setFlexibleManager';

        if ($this->definition->hasMethodCall($managerSetter)) {
            return;
        }

        $serviceId = $this->getFlexibleManagerServiceId();

        if ($serviceId) {
            $this->definition->addMethodCall($managerSetter, array(new Reference($serviceId)));
        }
    }

    /**
     * Get id of flexible manager service if it is appropriate
     *
     * @return string|null
     */
    protected function getFlexibleManagerServiceId()
    {
        if ($this->hasAttribute(self::FLEXIBLE_MANAGER_ATTRIBUTE)) {
            $result = $this->getAttribute(self::FLEXIBLE_MANAGER_ATTRIBUTE);
        } elseif ($this->hasAttribute(self::FLEXIBLE_ATTRIBUTE)) {
            $result = sprintf('%s.flexible_manager', $this->serviceId);
            $this->container->setDefinition($result, $this->createFlexibleManagerDefinition());
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Create flexible manager definition
     *
     * @return Definition
     */
    protected function createFlexibleManagerDefinition()
    {
        $definition = new Definition(self::FLEXIBLE_MANAGER_CLASS);
        $definition->setPublic(false);
        $definition->setFactoryService(self::FLEXIBLE_MANAGER_FACTORY_KEY);
        $definition->setFactoryMethod(self::FLEXIBLE_MANAGER_FACTORY_METHOD);
        $definition->setArguments(array($this->getMandatoryAttribute(self::ENTITY_NAME_ATTRIBUTE)));

        return $definition;
    }
}
