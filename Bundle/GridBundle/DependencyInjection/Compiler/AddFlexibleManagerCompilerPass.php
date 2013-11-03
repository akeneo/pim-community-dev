<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddFlexibleManagerCompilerPass extends AbstractDatagridManagerCompilerPass
{
    const FLEXIBLE_MANAGER_FACTORY_KEY    = 'oro_flexibleentity.registry';
    const FLEXIBLE_MANAGER_FACTORY_METHOD = 'getManager';
    const FLEXIBLE_MANAGER_CLASS          = 'Oro\\Bundle\\FlexibleEntityBundle\\Manager\\FlexibleManager';
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

        $flexibleManagerServiceId = $this->getFlexibleManagerServiceId();

        if ($flexibleManagerServiceId) {
            $this->definition->addMethodCall($managerSetter, array(new Reference($flexibleManagerServiceId)));
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
