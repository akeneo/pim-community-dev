<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\TransformBundle\Cache\EntityCache;
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
     * @var boolean
     */
    protected $multiple;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param EntityCache              $entityCache
     * @param ItemReaderInterface      $reader
     * @param ItemProcessorInterface   $processor
     * @param EventDispatcherInterface $eventDispatcher
     * @param boolean                  $multiple
     */
    public function __construct(
        ObjectManager $objectManager,
        EntityCache $entityCache,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        EventDispatcherInterface $eventDispatcher,
        $multiple
    ) {
        $this->objectManager = $objectManager;
        $this->entityCache = $entityCache;
        $this->reader = $reader;
        $this->processor = $processor;
        $this->eventDispatcher = $eventDispatcher;
        $this->multiple = $multiple;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        $this->eventDispatcher->dispatch(static::EVENT_STARTED, new FixtureLoaderEvent($file));
        $this->reader->setFilePath($file);

        if ($this->multiple) {
            $items = $this->reader->read();
            foreach ($this->processor->process($items) as $object) {
                $this->persistObject($object);
            }
        } else {
            while ($item = $this->reader->read()) {
                $this->persistObject($this->processor->process($item));
            }
        }

        $this->objectManager->flush();
        $this->objectManager->clear();
        $this->entityCache->clear();

        $this->eventDispatcher->dispatch(static::EVENT_COMPLETED, new FixtureLoaderEvent($file));
    }

    /**
     * Persists an object
     *
     * @param object $object
     */
    protected function persistObject($object)
    {
        $this->objectManager->persist($object);
        $this->entityCache->setReference($object);
    }
}
