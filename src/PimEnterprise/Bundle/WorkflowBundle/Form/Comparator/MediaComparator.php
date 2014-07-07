<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Comparator which calculate change set for medias
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @see       PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class MediaComparator implements ComparatorInterface
{
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
    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        if ((isset($submittedData['media']['file']) && $submittedData['media']['file'] instanceof UploadedFile)
            || isset($submittedData['media']['removed'])) {
            return $submittedData;
        }
    }
}
