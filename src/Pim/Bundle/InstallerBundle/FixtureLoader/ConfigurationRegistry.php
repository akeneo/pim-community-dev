<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
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
     * @var string
     */
    protected $cacheDir;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param ContainerInterface        $container
     * @param PropertyAccessorInterface $propertyAccessor
     * @param array                     $bundles
     * @param string                    $cacheDir
     * @param boolean                   $debug
     */
    public function __construct(
        ContainerInterface $container,
        PropertyAccessorInterface $propertyAccessor,
        $bundles,
        $cacheDir,
        $debug
    ) {
        $this->container = $container;
        $this->propertyAccessor = $propertyAccessor;
        $this->bundles = $bundles;
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
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
    public function getFixtures(array $filePaths)
    {
        $ordered = array();

        foreach ($filePaths as $filePath) {
            if (!is_dir($filePath)) {
                $this->setFixtures($ordered, $filePath);
            }
        }

        ksort($ordered);
        $returned = array();
        foreach ($ordered as $fixtures) {
            $returned = array_merge($returned, $fixtures);
        }

        return $returned;
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

    /**
     * Adds fixtures in an array for given file path
     *
     * @param array  &$ordered
     * @param string $filePath
     */
    protected function setFixtures(array &$ordered, $filePath)
    {
        $pathInfo = pathinfo($filePath);
        foreach ($this->getConfiguration() as $fixtureName => $fixtureConfig) {
            if (!isset($fixtureConfig[$pathInfo['extension']])) {
                continue;
            }

            $fixtureFileName = isset($fixtureConfig['file_name']) ? $fixtureConfig['file_name'] : $fixtureName;
            if ($fixtureFileName != $pathInfo['filename']) {
                continue;
            }

            $order = $this->getConfigProperty($fixtureName, 'order');
            if (!isset($ordered[$order])) {
                $ordered[$order] = array();
            }
            $ordered[$order][] = array(
                'path'      => $filePath,
                'name'      => $fixtureName,
                'extension' => $pathInfo['extension']
            );
        }
    }

    /**
     * Get a configuration property
     *
     * @param string $name
     * @param string $property
     *
     * @return string
     */
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
        $cachePath = $this->cacheDir . '/pim_fixtures.php';
        if (!isset($this->config)) {
            $configCache = new ConfigCache($cachePath, $this->debug);
            if ($configCache->isFresh()) {
                $this->config = include $cachePath;
            } else {
                $this->config = $this->parseConfiguration($configCache);
            }
        }

        return $this->config;
    }

    /**
     * Parses the configuration files
     *
     * @param ConfigCache $configCache
     *
     * @return array
     */
    protected function parseConfiguration(ConfigCache $configCache)
    {
        $config = array();
        $resources = array();
        foreach ($this->bundles as $class) {
            $reflection = new \ReflectionClass($class);
            $path = dirname($reflection->getFileName()) . '/Resources/config/fixtures.yml';
            if (file_exists($path)) {
                $config = Yaml::parse($path) + $config;
                $resources[] = new FileResource($path);
            }
        }
        $configCache->write('<?php return ' . var_export($config, true) . ';', $resources);

        return $config;
    }
}
