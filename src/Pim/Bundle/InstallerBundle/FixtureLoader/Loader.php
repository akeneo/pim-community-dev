<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;
use Pim\Bundle\InstallerBundle\Event\FixtureLoaderEvent;

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
     * @staticvar string Start event name
     */
    const EVENT_STARTED = 'pim_installer.installer.fixture_loader.start';

    /**
     * @staticvar string End event name
     */
    const EVENT_COMPLETED = 'pim_installer.installer.fixture_loader.end';

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param ReferenceRepository      $referenceRepository
     * @param EntityCache              $entityCache
     * @param ItemReaderInterface      $reader
     * @param ItemProcessorInterface   $processor
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        ReferenceRepository $referenceRepository,
        EntityCache $entityCache,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->referenceRepository = $referenceRepository;
        $this->entityCache = $entityCache;
        $this->reader = $reader;
        $this->processor = $processor;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        $this->eventDispatcher->dispatch(static::EVENT_STARTED, new FixtureLoaderEvent($file));
        $this->reader->setFilePath($file);
        while ($data = $this->reader->read()) {
            $object = $this->processor->process($data);
            $this->objectManager->persist($object);
            $this->setReference($data, $object);
        }

        $this->objectManager->flush();
        $this->objectManager->clear();
        $this->entityCache->clear();
        $this->eventDispatcher->dispatch(static::EVENT_COMPLETED, new FixtureLoaderEvent($file));
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
