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
    protected $fixtureReferenceTransformer;

    /**
     * @var ConfigurationRegistryInterface
     */
    protected $configurationRegistry;

    /**
     * Constructor
     *
     * @param EntityCache                    $entityCache
     * @param FixtureReferenceTransformer    $fixtureReferenceTransformer
     * @param ConfigurationRegistryInterface $configurationRegistry
     */
    public function __construct(
        EntityCache $entityCache,
        FixtureReferenceTransformer $fixtureReferenceTransformer,
        ConfigurationRegistryInterface $configurationRegistry
    ) {
        $this->entityCache = $entityCache;
        $this->fixtureReferenceTransformer = $fixtureReferenceTransformer;
        $this->configurationRegistry = $configurationRegistry;
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
        $this->fixtureReferenceTransformer->setReferenceRepository($referenceRepository);
        $reader = $this->configurationRegistry->getReader($name, $extension);
        $processor = $this->configurationRegistry->getProcessor($name, $extension);
        $class = $this->configurationRegistry->getClass($name);

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
