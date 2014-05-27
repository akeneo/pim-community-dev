<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Form\Comparator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MediaComparator implements ComparatorInterface
{
    protected $mediaManager;

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    public function supportsComparison(AbstractProductValue $value)
    {
        return in_array($value->getAttribute()->getAttributeType(), ['pim_catalog_file', 'pim_catalog_image']);
    }

    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        if (isset($submittedData['media']['file']) && $submittedData['media']['file'] instanceof UploadedFile) {
            $media = new Media();
            $media->setFile($submittedData['media']['file']);
            $this->mediaManager->handle($media, 'proposal-' . md5(time() . uniqid()));

            return [
                'media' => [
                    'originalFilename' => $media->getOriginalFilename(),
                    'filePath' => $media->getFilePath(),
                    'mimeType' => $media->getMimeType(),
                    'size' => $submittedData['media']['file']->getClientSize(),
                ]
            ];
        }
    }
}
