<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\CategoryEvents;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Category remover
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRemover implements RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var RemovingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /**
     * @param ObjectManager                    $objectManager
     * @param RemovingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface         $eventDispatcher
     * @param BulkSaverInterface               $productSaver
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        BulkSaverInterface $productSaver
    ) {
        $this->objectManager   = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->productSaver    = $productSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($category, array $options = [])
    {
        if (!$category instanceof CategoryInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    'Pim\Bundle\CatalogBundle\Model\CategoryInterface',
                    ClassUtils::getClass($category)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);
        $eventName = $category->isRoot() ? CategoryEvents::PRE_REMOVE_TREE : CategoryEvents::PRE_REMOVE_CATEGORY;
        $this->eventDispatcher->dispatch($eventName, new GenericEvent($category));

        $productsToUpdate = [];
        foreach ($category->getProducts() as $product) {
            $product->removeCategory($category);
            $productsToUpdate[] = $product;
        }

        $this->objectManager->remove($category);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (count($productsToUpdate) > 0) {
            $this->productSaver->saveAll(
                $productsToUpdate,
                [
                    'flush' => $options['flush'],
                    'recalculate' => false,
                    'schedule' => false,
                ]
            );
        }
    }
}
