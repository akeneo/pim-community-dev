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
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Saver for an asset reference
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AssetReferenceSaver implements SaverInterface, BulkSaverInterface
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
    public function save($reference, array $options = [])
    {
        $this->validateReference($reference);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($reference, $options));

        $this->objectManager->persist($reference);
        $this->objectManager->flush();
        $this->compGenerator->scheduleForAsset($reference->getAsset());

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($reference, $options));
    }

    /**
     * Save many objects
     *
     * @param ReferenceInterface[] $references
     * @param array                $options    The saving options
     */
    public function saveAll(array $references, array $options = [])
    {
        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($references, $options));

        foreach ($references as $reference) {
            $this->validateReference($reference);
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($reference, $options));
            $this->objectManager->persist($reference);
        }

        $this->objectManager->flush();

        foreach ($references as $reference) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($reference, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($references, $options));
    }

    /**
     * @param object $reference
     *
     * @throws \InvalidArgumentException
     */
    protected function validateReference($reference)
    {
        if (!$reference instanceof ReferenceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\ProductAsset\Model\ReferenceInterface", "%s" provided.',
                    ClassUtils::getClass($reference)
                )
            );
        }
    }
}
