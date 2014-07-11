<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * Store product value changes and some metadata
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChangesCollector implements ChangesCollectorInterface
{
    /** @var MediaManager */
    protected $mediaManager;

    /** @var array */
    protected $changes;

    /** @var array */
    protected $keysToRemove = [];

    /**
     * Construct
     *
     * @param MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add($key, $changes, AbstractProductValue $value)
    {
        $id = $value->getEntity()->getId();
        if (isset($this->changes[$id]['values'][$key])) {
            // Someone has already defined the changes applied to $key
            return;
        }

        // Convert uploaded file before storing media changes
        if (isset($changes['media']['file']) && $changes['media']['file'] instanceof UploadedFile) {
            $media = new Media();
            $media->setFile($changes['media']['file']);
            $this->mediaManager->handle($media, 'proposition-' . md5(time() . uniqid()));

            $changes['media']['filename'] = $media->getFilename();
            $changes['media']['originalFilename'] = $media->getOriginalFilename();
            $changes['media']['filePath'] = $media->getFilePath();
            $changes['media']['mimeType'] = $media->getMimeType();
            $changes['media']['size'] = $changes['media']['file']->getClientSize();

            unset($changes['media']['file']);
        }

        // TODO (2014-07-03 10:15 by Gildas): Store data and metadata in 2 differents structures
        $this->changes['values'][$key] = array_merge(
            $changes,
            [
                '__context__' => [
                    'attribute' => $value->getAttribute()->getCode(),
                    'locale' => $value->getLocale(),
                    'scope' => $value->getScope(),
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductInterface $product)
    {
        $id = $product->getId();
        if (isset($this->changes[$id])) {
            return $this->changes[$id];
        }

        return [];
    }
}
