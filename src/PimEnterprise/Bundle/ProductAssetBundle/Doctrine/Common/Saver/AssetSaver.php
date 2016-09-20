<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Saver for an asset
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CompletenessGeneratorInterface */
    protected $compGenerator;

    /**
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param CompletenessGeneratorInterface $compGenerator
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CompletenessGeneratorInterface $compGenerator
    ) {
        $this->objectManager   = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->compGenerator   = $compGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function save($asset, array $options = [])
    {
        $this->validateAsset($asset);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($asset, $options));

        $this->objectManager->persist($asset);
        $this->objectManager->flush();
        $this->compGenerator->scheduleForAsset($asset);

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($asset, $options));
    }

    /**
     * @param AssetInterface[] $assets
     * @param array            $options
     */
    public function saveAll(array $assets, array $options = [])
    {
        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($assets, $options));

        foreach ($assets as $asset) {
            $this->validateAsset($asset);
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($asset, $options));
            $this->objectManager->persist($asset);
        }

        $this->objectManager->flush();

        foreach ($assets as $asset) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($asset, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($assets, $options));
    }

    /**
     * @param object $asset
     *
     * @throws \InvalidArgumentException
     */
    protected function validateAsset($asset)
    {
        if (!$asset instanceof AssetInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\AssetInterface", "%s" provided.',
                    ClassUtils::getClass($asset)
                )
            );
        }
    }
}
