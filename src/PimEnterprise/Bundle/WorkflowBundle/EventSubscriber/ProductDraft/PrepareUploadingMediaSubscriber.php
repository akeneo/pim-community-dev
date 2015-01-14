<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\UploadedFileFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Prepare the data before submitting them to the form
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PrepareUploadingMediaSubscriber implements EventSubscriberInterface
{
    /** @var UploadedFileFactory */
    protected $factory;

    /**
     * @param UploadedFileFactory $factory
     */
    public function __construct(UploadedFileFactory $factory = null)
    {
        $this->factory = $factory ?: new UploadedFileFactory();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductDraftEvents::PRE_APPLY => 'prepareMedia'
        ];
    }

    /**
     * Convert uploading media into object
     *
     * @param ProductDraftEvent $event
     */
    public function prepareMedia(ProductDraftEvent $event)
    {
        $productDraft = $event->getProductDraft();
        $changes = $productDraft->getChanges();

        if (!isset($changes['values'])) {
            return;
        }

        foreach ($changes['values'] as $key => $change) {
            if (isset($change['media']) && $this->isUploadingMedia($change['media'])) {
                if (null !== $file = $this->factory->create(
                    $change['media']['filePath'],
                    $change['media']['originalFilename'],
                    $change['media']['mimeType'],
                    $change['media']['size']
                )) {
                    $changes['values'][$key]['media'] = [
                        'file' => $file,
                    ];
                }
            }
        }

        $productDraft->setChanges($changes);
    }

    /**
     * Wether or not media data should be converted into uploaded file
     *
     * @param array $media
     *
     * @return boolean
     */
    protected function isUploadingMedia(array $media)
    {
        foreach (['filePath', 'originalFilename', 'mimeType', 'size'] as $mediaKey) {
            if (!array_key_exists($mediaKey, $media)) {
                return false;
            }
        }

        return true;
    }
}
