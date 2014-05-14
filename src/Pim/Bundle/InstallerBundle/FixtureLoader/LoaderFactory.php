<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

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
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * @var ConfigurationRegistryInterface
     */
    protected $configRegistry;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param DoctrineCache                  $doctrineCache
     * @param ConfigurationRegistryInterface $configRegistry
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        DoctrineCache $doctrineCache,
        ConfigurationRegistryInterface $configRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->doctrineCache = $doctrineCache;
        $this->configRegistry = $configRegistry;
        $this->eventDispatcher = $eventDispatcher;
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
        $this->doctrineCache->setReferenceRepository($referenceRepository);
        $reader = $this->configRegistry->getReader($name, $extension);
        $processor = $this->configRegistry->getProcessor($name, $extension);
        $class = $this->configRegistry->getClass($name);
        $multiple = $this->configRegistry->isMultiple($name);
        $productManager = $this->configRegistry->getProductManager();

        return $this->createLoader($objectManager, $reader, $processor, $class, $multiple, $productManager);
    }

    /**
     * Creates a loader
     *
     * @param ObjectManager          $objectManager
     * @param ItemReaderInterface    $reader
     * @param ItemProcessorInterface $processor
     * @param string                 $class
     * @param boolean                $multiple
     * @param ProductManager         $productManager
     *
     * @return LoaderInterface
     */
    protected function createLoader(
        ObjectManager $objectManager,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        $class,
        $multiple,
        ProductManager $productManager
    ) {
        return new $class(
            $objectManager,
            $this->doctrineCache,
            $reader,
            $processor,
            $this->eventDispatcher,
            $multiple,
            $productManager
        );
    }
}
