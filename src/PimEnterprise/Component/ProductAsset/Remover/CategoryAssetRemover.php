<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Remover;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Bundle\ProductAssetBundle\Event\CategoryAssetEvents;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Category asset remover
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class CategoryAssetRemover implements RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var RemovingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var BulkSaverInterface */
    protected $assetSaver;

    /**
     * @param ObjectManager                    $objectManager
     * @param RemovingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface         $eventDispatcher
     * @param BulkSaverInterface               $assetSaver
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        BulkSaverInterface $assetSaver
    ) {
        $this->objectManager   = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->assetSaver      = $assetSaver;
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
                    'PimEnterprise\Component\ProductAsset\Model\CategoryInterface',
                    ClassUtils::getClass($category)
                )
            );
        }

        $options = $this->optionsResolver->resolveRemoveOptions($options);

        $categoryId = $category->getId();
        $eventName = $category->isRoot() ?
            CategoryAssetEvents::PRE_REMOVE_TREE :
            CategoryAssetEvents::PRE_REMOVE_CATEGORY;
        $this->eventDispatcher->dispatch($eventName, new RemoveEvent($category, $categoryId));

        $assetsToUpdate = [];
        $assets = $category->getAssets();
        if (!empty($assets)) {
            foreach ($assets as $asset) {
                $asset->removeCategory($category);
                $assetsToUpdate[] = $asset;
            }
        }

        $this->objectManager->remove($category);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (count($assetsToUpdate) > 0) {
            $this->assetSaver->saveAll(
                $assetsToUpdate,
                [
                    'flush' => $options['flush'],
                ]
            );
        }

        $eventName = $category->isRoot() ?
            CategoryAssetEvents::POST_REMOVE_TREE :
            CategoryAssetEvents::POST_REMOVE_CATEGORY;
        $this->eventDispatcher->dispatch($eventName, new RemoveEvent($category, $categoryId));
    }
}
