<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractDatagridManagerCompilerPass implements CompilerPassInterface
{
    const DATAGRID_MANAGER_TAG = 'oro_grid.datagrid.manager';

    /**
     * Current container builder
     *
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Current service id
     *
     * @var string
     */
    protected $serviceId;

    /**
     * Current attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Current datagrid manager definition
     *
     * @var Definition
     */
    protected $definition;

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->container = $container;
        foreach ($container->findTaggedServiceIds(self::DATAGRID_MANAGER_TAG) as $serviceId => $tags) {
            $this->serviceId = $serviceId;
            $this->definition = $this->container->getDefinition($this->serviceId);
            foreach ($tags as $attributes) {
                $this->attributes = $attributes;
                $this->processDatagrid();
            }
        }
    }

    /**
     * Process datagrid service definition
     */
    abstract protected function processDatagrid();

    /**
     * Checks if attributes has a key
     *
     * @param string $key
     * @return bool
     */
    protected function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get attribute value by key
     *
     * @param string $key
     * @return bool
     */
    protected function getAttribute($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    /**
     * Get attribute value by key but asserts that it exists
     *
     * @param string $key
     * @return bool
     */
    protected function getMandatoryAttribute($key)
    {
        $this->assertHasAttribute($key);
        return $this->getAttribute($key);
    }

    /**
     * Asserts that attributes has a key
     *
     * @param string $key
     * @return bool
     * @throws InvalidDefinitionException If attribute is not exist
     */
    protected function assertHasAttribute($key)
    {
        if (!$this->hasAttribute($key)) {
            throw new InvalidDefinitionException(
                sprintf(
                    'Definition of service "%s" must have "%s" attribute in tag "%s"',
                    $this->serviceId,
                    $key,
                    self::DATAGRID_MANAGER_TAG
                )
            );
        }
    }

    /**
     * Method taken from PropertyPath
     *
     * @param string $property
     * @return mixed
     */
    protected function camelize($property)
    {
        return Container::camelize($property);
    }
}
