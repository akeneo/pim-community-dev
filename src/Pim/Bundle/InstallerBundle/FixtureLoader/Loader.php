<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\InstallerBundle\Event\FixtureLoaderEvent;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var boolean
     */
    protected $multiple;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager
     * @param DoctrineCache            $doctrineCache
     * @param ItemReaderInterface      $reader
     * @param ItemProcessorInterface   $processor
     * @param EventDispatcherInterface $eventDispatcher
     * @param boolean                  $multiple
     * @param ProductManager           $productManager
     */
    public function __construct(
        ObjectManager $objectManager,
        DoctrineCache $doctrineCache,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        EventDispatcherInterface $eventDispatcher,
        $multiple,
        ProductManager $productManager
    ) {
        $this->objectManager = $objectManager;
        $this->doctrineCache = $doctrineCache;
        $this->reader = $reader;
        $this->processor = $processor;
        $this->eventDispatcher = $eventDispatcher;
        $this->multiple = $multiple;
        $this->productManager = $productManager;
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
                $this->persistObjects($object);
            }
        } else {
            while ($item = $this->reader->read()) {
                $this->persistObjects($this->processor->process($item));
            }
        }

        $this->objectManager->flush();
        $this->objectManager->clear();
        $this->doctrineCache->clear();

        $this->eventDispatcher->dispatch(static::EVENT_COMPLETED, new FixtureLoaderEvent($file));
    }

    /**
     * Persists objects
     *
     * @param object|array $objects
     */
    protected function persistObjects($objects)
    {
        if (is_array($objects)) {
            foreach ($objects as $object) {
                $this->persistObject($object);
            }
        } else {
            $this->persistObject($objects);
        }
    }

    /**
     * Persists an object
     *
     * @param object $object
     */
    protected function persistObject($object)
    {
        if ($object instanceof \Pim\Bundle\CatalogBundle\Model\ProductInterface) {
            $this->productManager->handleMedia($object);
        }
        $this->objectManager->persist($object);
        $this->doctrineCache->setReference($object);
    }
}
