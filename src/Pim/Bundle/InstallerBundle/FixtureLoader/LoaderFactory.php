<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;
use Pim\Bundle\InstallerBundle\Transformer\Property\FixtureReferenceTransformer;

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
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * @var FixtureReferenceTransformer
     */
    protected $referenceTransformer;

    /**
     * @var ConfigurationRegistryInterface
     */
    protected $configRegistry;

    /**
     * Constructor
     *
     * @param EntityCache                    $entityCache
     * @param FixtureReferenceTransformer    $referenceTransformer
     * @param ConfigurationRegistryInterface $configRegistry
     */
    public function __construct(
        EntityCache $entityCache,
        FixtureReferenceTransformer $referenceTransformer,
        ConfigurationRegistryInterface $configRegistry
    ) {
        $this->entityCache = $entityCache;
        $this->referenceTransformer = $referenceTransformer;
        $this->configRegistry = $configRegistry;
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
        $this->referenceTransformer->setReferenceRepository($referenceRepository);
        $reader = $this->configRegistry->getReader($name, $extension);
        $processor = $this->configRegistry->getProcessor($name, $extension);
        $class = $this->configRegistry->getClass($name);

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
}
