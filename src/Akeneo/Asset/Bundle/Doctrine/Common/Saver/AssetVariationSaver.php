<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Doctrine\Common\Saver;

use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
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

    /** @var CompletenessRemoverInterface */
    protected $completenessRemover;

    /**
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param CompletenessRemoverInterface $completenessRemover
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CompletenessRemoverInterface $completenessRemover
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->completenessRemover = $completenessRemover;
    }

    /**
     * {@inheritdoc}
     */
    public function save($variation, array $options = [])
    {
        $this->validateVariation($variation);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($variation, $options));

        $this->objectManager->persist($variation);
        $this->completenessRemover->removeForAsset($variation->getAsset());
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
        $options['unitary'] = false;

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
                    'Expects a "Akeneo\Asset\Component\Model\VariationInterface", "%s" provided.',
                    ClassUtils::getClass($variation)
                )
            );
        }
    }
}
