<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;
use Pim\Bundle\InstallerBundle\Transformer\Property\FixtureReferenceTransformer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Fixture Loader  factory
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoaderFactory
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
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * @var FixtureReferenceTransformer
     */
    protected $fixtureReferenceTransformer;

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
     * @param ContainerInterface          $container
     * @param EntityCache                 $entityCache
     * @param FixtureReferenceTransformer $fixtureReferenceTransformer
     * @param PropertyAccessorInterface   $propertyAccessor
     * @param array                       $bundles
     */
    public function __construct(
        ContainerInterface $container,
        EntityCache $entityCache,
        FixtureReferenceTransformer $fixtureReferenceTransformer,
        PropertyAccessorInterface $propertyAccessor,
        array $bundles
    ) {
        $this->container = $container;
        $this->entityCache = $entityCache;
        $this->fixtureReferenceTransformer = $fixtureReferenceTransformer;
        $this->propertyAccessor = $propertyAccessor;
        $this->bundles = $bundles;
    }

    /**
     * Returns the fixture loading order for an entity
     *
     * @param string $name
     *
     * @return int
     */
    public function getOrder($name)
    {
        $config = $this->getConfig();

        return isset($config[$name]['order']) ? $config[$name]['order'] : $config['default']['order'];
    }

    /**
     * Creates a loader
     *
     * @param ObjectManager       $objectManager
     * @param ReferenceRepository $referenceRepository
     * @param string              $name
     * @param string              $extension
     *
     * @return LoaderInterface
     */
    public function create(ObjectManager $objectManager, ReferenceRepository $referenceRepository, $name, $extension)
    {
        $config = $this->getConfig();
        if (!isset($config[$name])) {
            return;
        }
        $this->fixtureReferenceTransformer->setReferenceRepository($referenceRepository);
        $defaultConfig = $config['default'];
        $entityConfig = $config[$name];
        $reader = $this->getReader($extension, $entityConfig, $defaultConfig);
        $processor = $this->getProcessor($extension,  $entityConfig, $defaultConfig);
        $class = isset($entityConfig['class']) ? $entityConfig['class'] : $defaultConfig['class'];

        return $this->createLoader($objectManager, $referenceRepository, $reader, $processor, $class);
    }

    /**
     * Creates a loader
     *
     * @param ObjectManager          $objectManager
     * @param ReferenceRepository    $referenceRepository
     * @param ItemReaderInterface    $reader
     * @param ItemProcessorInterface $processor
     * @param string                 $class
     *
     * @return LoaderInterface
     */
    protected function createLoader(
        ObjectManager $objectManager,
        ReferenceRepository $referenceRepository,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        $class
    ) {
        return new $class(
            $objectManager,
            $referenceRepository,
            $this->entityCache,
            $reader,
            $processor
        );
    }

    /**
     * Returns the processor service for a given extension and configuration
     *
     * @param string $extension
     * @param array  $config
     * @param array  $defaultConfig
     *
     * @return ItemProcessorInterface
     */
    protected function getProcessor($extension, array $config, array $defaultConfig)
    {
        return $this->getFixtureService('processor', $extension, $config, $defaultConfig);
    }

    /**
     * Returns the reader service for a given extension and configuration
     *
     * @param string $extension
     * @param array  $config
     * @param array  $defaultConfig
     *
     * @return ItemReaderInterface
     */
    protected function getReader($extension, array $config, array $defaultConfig)
    {
        return $this->getFixtureService('reader', $extension, $config, $defaultConfig);
    }

    /**
     * Return a fixture service
     *
     * @param  string $service
     * @param  string $extension
     * @param  array  $config
     * @param  array  $defaultConfig
     * @return object
     */
    protected function getFixtureService($service, $extension, array $config, array $defaultConfig)
    {
        $extensionConfig = $config[$extension];
        $defaultExtensionConfig = $defaultConfig[$extension];
        $serviceId = isset($extensionConfig[$service])
            ? $extensionConfig[$service]
            : $defaultExtensionConfig[$service];

        $parametersKey = $service.'_options';
        $parameters = isset($extensionConfig[$parametersKey])
            ? $extensionConfig[$parametersKey]
            : $defaultExtensionConfig[$parametersKey];

        $service = $this->container->get($serviceId);
        foreach ($parameters as $propertyPath => $value) {
            $this->propertyAccessor->setValue($service, $propertyPath, $value);
        }

        return $service;
    }

    /**
     * Returns the configuration
     *
     * @return array
     */
    protected function getConfig()
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
