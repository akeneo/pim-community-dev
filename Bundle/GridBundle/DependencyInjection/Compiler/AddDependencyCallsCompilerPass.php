<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;

use Oro\Bundle\GridBundle\DependencyInjection\OroGridExtension;

class AddDependencyCallsCompilerPass extends AbstractDatagridManagerCompilerPass
{
    const REGISTRY_SERVICE             = 'oro_grid.datagrid_manager.registry';

    const QUERY_FACTORY_ATTRIBUTE      = 'query_factory';
    const ROUTE_GENERATOR_ATTRIBUTE    = 'route_generator';
    const DATAGRID_BUILDER_ATTRIBUTE   = 'datagrid_builder';
    const LIST_BUILDER_ATTRIBUTE       = 'list_builder';
    const PARAMETERS_ATTRIBUTE         = 'parameters';
    const TRANSLATOR_ATTRIBUTE         = 'translator';
    const TRANSLATION_DOMAIN_ATTRIBUTE = 'translation_domain';
    const VALIDATOR_ATTRIBUTE          = 'validator';
    const ROUTER_ATTRIBUTE             = 'router';
    const ENTITY_HINT_ATTRIBUTE        = 'entity_hint';

    /**
     * @var Definition
     */
    protected $registryDefinition;

    /**
     * {@inheritDoc}
     */
    public function processDatagrid()
    {
        $this->registryDefinition = $this->container->getDefinition(self::REGISTRY_SERVICE);

        $this->applyConfigurationFromAttributes();
        $this->applyDefaults();
    }

    /**
     * This method read the attribute keys and configure grid manager class to use the related dependency
     */
    protected function applyConfigurationFromAttributes()
    {
        $datagridName = $this->getMandatoryAttribute('datagrid_name');
        $this->definition->addMethodCall('setName', array($datagridName));

        // add service to datagrid manager registry
        $this->registryDefinition->addMethodCall('addDatagridManagerService', array($datagridName, $this->serviceId));

        // add services
        $serviceKeys = array(
            self::QUERY_FACTORY_ATTRIBUTE,
            self::ROUTE_GENERATOR_ATTRIBUTE,
            self::DATAGRID_BUILDER_ATTRIBUTE,
            self::LIST_BUILDER_ATTRIBUTE,
            self::PARAMETERS_ATTRIBUTE,
            self::TRANSLATOR_ATTRIBUTE,
            self::VALIDATOR_ATTRIBUTE,
            self::ROUTER_ATTRIBUTE,
        );

        foreach ($serviceKeys as $key) {
            $method = 'set' . $this->camelize($key);
            if (!$this->hasAttribute($key) || $this->definition->hasMethodCall($method)) {
                continue;
            }

            $this->definition->addMethodCall($method, array(new Reference($this->getAttribute($key))));
        }

        // add other attributes
        $attributeKeys = array(
            self::ENTITY_HINT_ATTRIBUTE,
            self::TRANSLATION_DOMAIN_ATTRIBUTE
        );

        foreach ($attributeKeys as $key) {
            $method = 'set' . $this->camelize($key);
            if (!$this->hasAttribute($key) || $this->definition->hasMethodCall($method)) {
                continue;
            }

            $this->definition->addMethodCall($method, array($this->getAttribute($key)));
        }
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition
     */
    protected function applyDefaults()
    {
        $this->definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        // add default services
        $defaultAddServices = array(
            self::QUERY_FACTORY_ATTRIBUTE    => array($this, 'getDefaultQueryFactoryServiceId'),
            self::ROUTE_GENERATOR_ATTRIBUTE  => array($this, 'getDefaultRouteGeneratorServiceId'),
            self::PARAMETERS_ATTRIBUTE       => array($this, 'getDefaultParametersServiceId'),
            self::DATAGRID_BUILDER_ATTRIBUTE => 'oro_grid.builder.datagrid',
            self::LIST_BUILDER_ATTRIBUTE     => 'oro_grid.builder.list',
            self::TRANSLATOR_ATTRIBUTE       => 'translator',
            self::VALIDATOR_ATTRIBUTE        => 'validator',
            self::ROUTER_ATTRIBUTE           => 'router',
        );

        foreach ($defaultAddServices as $attribute => $serviceId) {
            $method = 'set' . $this->camelize($attribute);

            if (!$this->definition->hasMethodCall($method)) {
                if (is_callable($serviceId)) {
                    $serviceId = call_user_func($serviceId);
                }
                $this->definition->addMethodCall($method, array(new Reference($serviceId)));
            }
        }

        // add default parameters
        $defaultAddParameters = array(
            self::TRANSLATION_DOMAIN_ATTRIBUTE => OroGridExtension::PARAMETER_TRANSLATION_DOMAIN
        );

        foreach ($defaultAddParameters as $attribute => $parameterId) {
            $method = 'set' . $this->camelize($attribute);

            if (!$this->definition->hasMethodCall($method)) {
                $this->definition->addMethodCall($method, array(new Parameter($parameterId)));
            }
        }
    }

    /**
     * Get id of default query factory service
     *
     * @return string
     */
    protected function getDefaultQueryFactoryServiceId()
    {
        $queryFactoryServiceId = sprintf('%s.default_query_factory', $this->serviceId);
        $this->container->setDefinition($queryFactoryServiceId, $this->createDefaultQueryFactoryDefinition());
        return $queryFactoryServiceId;
    }

    /**
     * Create default query factory service definition
     *
     * @return Definition
     * @throws InvalidDefinitionException
     */
    protected function createDefaultQueryFactoryDefinition()
    {
        $arguments = array();
        if ($this->hasAttribute('entity_name')) {
            $queryFactoryClass = '%oro_grid.orm.query_factory.entity.class%';

            $arguments = array(
                new Reference('doctrine'),
                $this->getAttribute('entity_name'),
            );

            if ($this->hasAttribute('query_entity_alias')) {
                $arguments[] = $this->getAttribute('query_entity_alias');
            }
        } else {
            $queryFactoryClass = '%oro_grid.orm.query_factory.query.class%';
        }

        $definition = new Definition($queryFactoryClass);
        $definition->setPublic(false);
        $definition->setArguments($arguments);

        return $definition;
    }

    /**
     * Get id of default route generator service
     *
     * @return string
     */
    protected function getDefaultRouteGeneratorServiceId()
    {
        $routeGeneratorServiceId = sprintf('%s.route.default_generator', $this->serviceId);
        $this->container->setDefinition($routeGeneratorServiceId, $this->createDefaultRouteGeneratorDefinition());
        return $routeGeneratorServiceId;
    }

    /**
     * Create default query factory service definition
     *
     * @return Definition
     * @throws InvalidDefinitionException
     */
    protected function createDefaultRouteGeneratorDefinition()
    {
        $arguments = array(
            new Reference('router'),
            $this->getMandatoryAttribute('route_name')
        );

        $definition = new Definition('%oro_grid.route.default_generator.class%');
        $definition->setPublic(false);
        $definition->setArguments($arguments);

        return $definition;
    }

    /**
     * Get id of default parameters service
     *
     * @return string
     */
    protected function getDefaultParametersServiceId()
    {
        $routeGeneratorServiceId = sprintf('%s.parameters.default', $this->serviceId);
        $this->container->setDefinition($routeGeneratorServiceId, $this->createDefaultParametersDefinition());
        return $routeGeneratorServiceId;
    }

    /**
     * Create default query factory service definition
     *
     * @return Definition
     * @throws InvalidDefinitionException
     */
    protected function createDefaultParametersDefinition()
    {
        $arguments = array(
            new Reference('service_container'),
            $this->getMandatoryAttribute('datagrid_name')
        );

        $definition = new Definition('%oro_grid.datagrid.parameters.class%');
        $definition->setPublic(false);
        $definition->setArguments($arguments);

        return $definition;
    }
}
