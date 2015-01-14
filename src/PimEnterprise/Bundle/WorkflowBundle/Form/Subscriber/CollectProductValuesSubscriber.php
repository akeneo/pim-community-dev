<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * A collector of changes that a client is sending to a product edit form
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class CollectProductValuesSubscriber implements EventSubscriberInterface
{
    /** @var ChangesCollector */
    protected $collector;

    /** @var MediaManager */
    protected $mediaManager;

    /** @var MediaFactory */
    protected $factory;

    /**
     * @param ChangesCollector $collector
     * @param MediaManager     $mediaManager
     * @param MediaFactory     $factory
     */
    public function __construct(
        ChangesCollector $collector,
        MediaManager $mediaManager,
        MediaFactory $factory
    ) {
        $this->collector = $collector;
        $this->mediaManager = $mediaManager;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'collect',
        ];
    }

    /**
     * Collect changes that client sent to the product values
     *
     * @param FormEvent $event
     */
    public function collect(FormEvent $event)
    {
        $data = $event->getData();

        if (!array_key_exists('values', $data)) {
            return;
        }

        foreach ($data['values'] as $key => $changes) {
            if (isset($changes['media']['file']) && $changes['media']['file'] instanceof UploadedFile) {
                $media = $this->factory->createMedia($changes['media']['file']);
                $this->mediaManager->handle($media, 'product-draft-' . md5(time() . uniqid()));

                $data['values'][$key]['media']['filename'] = $media->getFilename();
                $data['values'][$key]['media']['originalFilename'] = $media->getOriginalFilename();
                $data['values'][$key]['media']['filePath'] = $this->mediaManager->getFilePath($media);
                $data['values'][$key]['media']['mimeType'] = $media->getMimeType();
                $data['values'][$key]['media']['size'] = $changes['media']['file']->getClientSize();

                unset($data['values'][$key]['media']['file']);
            }
        }

        $this->collector->setData($data);
    }
}
