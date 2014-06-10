<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * Comparator which calculate change set for medias
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MediaComparator extends AbstractComparator
{
    /** @var MediaManager */
    protected $mediaManager;

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
    public function supportsComparison(AbstractProductValue $value)
    {
        return in_array($value->getAttribute()->getAttributeType(), ['pim_catalog_file', 'pim_catalog_image']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChanges(AbstractProductValue $value, $submittedData)
    {
        $changes = [];

        if (isset($submittedData['media']['file']) && $submittedData['media']['file'] instanceof UploadedFile) {
            $media = new Media();
            $media->setFile($submittedData['media']['file']);
            $this->mediaManager->handle($media, 'proposition-' . md5(time() . uniqid()));

            $changes['media'] = [
                'filename' => $media->getFilename(),
                'originalFilename' => $media->getOriginalFilename(),
                'filePath' => $media->getFilePath(),
                'mimeType' => $media->getMimeType(),
                'size' => $submittedData['media']['file']->getClientSize(),
            ];
        }

        if (isset($submittedData['media']['removed'])) {
            $changes['media']['removed'] = true;
        }

        return $changes;
    }
}
