<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product saver, define custom logic and options for product saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                  $om
     * @param CompletenessManager            $completenessManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        ObjectManager $om,
        CompletenessManager $completenessManager,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager       = $om;
        $this->completenessManager = $completenessManager;
        $this->optionsResolver     = $optionsResolver;
        $this->eventDispatcher     = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Component\Catalog\Model\ProductInterface, "%s" provided',
                    ClassUtils::getClass($product)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));


        $this->objectManager->persist($product);

        if (true === $options['schedule'] || true === $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if (true === $options['recalculate'] || true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (true === $options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
        /*
         * A.
         * If I listen for this event on mass edit I will not have in the $event->getEventType() the real changeset
         * because the object was not flushed into the database.
         * Also it's still not so clear for me why the post_save event should be dispatched if you didn't flush the
         * object.
         * */
        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $options = $this->optionsResolver->resolveSaveAllOptions($options);
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($products, $options));

        $itemOptions = $options;
        $itemOptions['flush'] = false;

        foreach ($products as $product) {
            /*
            B.
            Here is where the save function is called multiple times for a batch when we use mass edit.
            The function save will dispatch post_save events without flushing the changes into the database.

            If saveAll function would not base on save function, everything should work as I expect:
            - saveAll will flush every batch and dispatch the post_save_all event, which I am listening when I do imports
            or mass edits.
            - save will flush every time and dispatch the events with the correct change set.
            */
            $this->save($product, $itemOptions);
        }

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
        /*
         * C.
         * 1. If we listen for both post_save and post_save_all events all the events are duplicated right now.
         * - From post_save I have the event with a wrong change set.
         * - From post_save_all I have the correct event.
         *
         * 2. If I change the save function to dispatch the event post_save only when the object is flushed (as in my
         * previous PR) and listen for both post_save and post_save_all, everything is awesome.
         * The only issue in that case is that on mass edit "Edit common attributes"
         * "PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\DelegatingProductSaver" does not raise
         * "pre/post_save_all" events at all on saveAll function.
         * */
        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($products, $options));
    }
}
