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
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Saver for an asset variation
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetVariationSaver implements SaverInterface, BulkSaverInterface
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
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->compGenerator = $compGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function save($variation, array $options = [])
    {
        $this->validateVariation($variation);
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($variation, $options));

        $this->objectManager->persist($variation);
        $this->compGenerator->scheduleForAsset($variation->getAsset());
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($variation, $options));
    }

    /**
     * Save many objects
     *
     * @param VariationInterface[] $variations
     * @param array                $options    The saving options
     */
    public function saveAll(array $variations, array $options = [])
    {
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($variations, $options));
        foreach ($variations as $variation) {
            $this->validateVariation($variation);
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($variation, $options));
            $this->objectManager->persist($variation);
        }
        $this->objectManager->flush();

        foreach ($variations as $variation) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($variation, $options));
        }
        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($variations, $options));
    }

    /**
     * @param object $variation
     *
     * @throws \InvalidArgumentException
     */
    protected function validateVariation($variation)
    {
        if (!$variation instanceof VariationInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\VariationInterface", "%s" provided.',
                    ClassUtils::getClass($variation)
                )
            );
        }
    }
}
