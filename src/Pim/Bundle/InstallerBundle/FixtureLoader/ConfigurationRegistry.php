<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Fixture loader configuration
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationRegistry implements ConfigurationRegistryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var array
     */
    protected $bundles;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param ContainerInterface        $container
     * @param PropertyAccessorInterface $propertyAccessor
     * @param array                     $bundles
     */
    public function __construct(ContainerInterface $container, PropertyAccessorInterface $propertyAccessor, $bundles)
    {
        $this->container = $container;
        $this->propertyAccessor = $propertyAccessor;
        $this->bundles = $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($name)
    {
        $config = $this->getConfiguration();

        return isset($config[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($name)
    {
        return $this->getConfigProperty($name, 'order');
    }

    /**
     * {@inheritdoc}
     */
    public function getClass($name)
    {
        return $this->getConfigProperty($name, 'class');
    }

    /**
     * {@inheritdoc}
     */
    public function isMultiple($name)
    {
        return $this->getConfigProperty($name, 'multiple');
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessor($name, $extension)
    {
        return $this->getFixtureService('processor', $name, $extension);
    }

    /**
     * {@inheritdoc}
     */
    public function getReader($name, $extension)
    {
        return $this->getFixtureService('reader', $name, $extension);
    }

    protected function getConfigProperty($name, $property)
    {
        $config = $this->getConfiguration();

        return isset($config[$name][$property]) ? $config[$name][$property] : $config['default'][$property];
    }

    /**
     * Return a fixture service
     *
     * @param string $service
     * @param string $name
     * @param string $extension
     *
     * @return object
     */
    protected function getFixtureService($service, $name, $extension)
    {
        $config = $this->getConfiguration();

        $defaultExtConfig = $config['default'][$extension];
        $extConfig = isset($config[$name][$extension]) ? $config[$name][$extension] : $defaultExtConfig;

        $serviceId = isset($extConfig[$service])
            ? $extConfig[$service]
            : $defaultExtConfig[$service];

        $parametersKey = $service.'_options';
        $parameters = isset($extConfig[$parametersKey])
            ? $extConfig[$parametersKey]
            : $defaultExtConfig[$parametersKey];

        $service = $this->container->get($serviceId);
        foreach ($parameters as $propertyPath => $value) {
            $this->propertyAccessor->setValue($service, $propertyPath, $value);
        }

        return $service;
    }
    /**
     * Returns the configuration from the bundles
     *
     * @return array
     */
    protected function getConfiguration()
    {
        if (!isset($this->config)) {
            $this->config = array();
            foreach ($this->bundles as $class) {
                $reflection = new \ReflectionClass($class);
                $path = dirname($reflection->getFileName()) . '/Resources/config/fixtures.yml';
                if (file_exists($path)) {
                    $this->config = Yaml::parse($path) + $this->config;
                }
            }
        }

        return $this->config;
    }
}
