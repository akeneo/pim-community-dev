<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

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
        if ($this->hasNewMedia($submittedData) || isset($submittedData['media']['removed'])) {
            return $submittedData;
        }
    }

    protected function hasNewMedia($data)
    {
        if (!isset($data['media'])) {
            return false;
        }

        foreach (['filename', 'originalFilename', 'filePath', 'mimeType', 'size'] as $key) {
            if (!array_key_exists($key, $data['media'])) {
                return false;
            }
        }

        return true;
    }
}
