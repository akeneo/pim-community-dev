<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Remover;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover\BaseRemover;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Asset remover extends BaseRemover in the goal to dispatch event before and after removal
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 *
 * TODO: missing spec
 */
class AssetRemover extends BaseRemover
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                    $objectManager
     * @param RemovingOptionsResolverInterface $optionsResolver
     * @param string                           $removedClass
     * @param EventDispatcherInterface         $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        $removedClass,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($objectManager, $optionsResolver, $eventDispatcher, $removedClass);

        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($asset, array $options = [])
    {
        $this->eventDispatcher->dispatch(AssetEvent::PRE_REMOVE, new AssetEvent($asset));
        parent::remove($asset, $options);
        $this->eventDispatcher->dispatch(AssetEvent::POST_REMOVE, new AssetEvent($asset));
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $assets, array $options = [])
    {
        $this->eventDispatcher->dispatch(AssetEvent::PRE_REMOVE, new AssetEvent($assets));
        parent::removeAll($assets, $options);
        $this->eventDispatcher->dispatch(AssetEvent::POST_REMOVE, new AssetEvent($assets));
    }
}
