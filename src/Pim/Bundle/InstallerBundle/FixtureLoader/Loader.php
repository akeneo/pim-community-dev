<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Fixture Loader
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Loader implements LoaderInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var ItemProcessorInterface
     */
    protected $processor;

    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * Constructor
     *
     * @param ObjectManager          $objectManager
     * @param ReferenceRepository    $referenceRepository
     * @param EntityCache            $entityCache
     * @param ItemReaderInterface    $reader
     * @param ItemProcessorInterface $processor
     */
    public function __construct(
        ObjectManager $objectManager,
        ReferenceRepository $referenceRepository,
        EntityCache $entityCache,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor
    ) {
        $this->objectManager = $objectManager;
        $this->referenceRepository = $referenceRepository;
        $this->entityCache = $entityCache;
        $this->reader = $reader;
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        $this->reader->setFilePath($file);
        while ($data = $this->reader->read()) {
            $object = $this->processor->process($data);
            $this->objectManager->persist($object);
            $this->setReference($data, $object);
        }

        $this->objectManager->flush();
        $this->objectManager->clear();
        $this->entityCache->clear();
    }

    /**
     * Sets a reference to the object
     *
     * @param array  $data
     * @param object $object
     */
    protected function setReference(array $data, $object)
    {
        if (isset($data['code'])) {
            $this->referenceRepository->addReference(get_class($object) . '.' . $data['code'], $object);
        }
    }
}
