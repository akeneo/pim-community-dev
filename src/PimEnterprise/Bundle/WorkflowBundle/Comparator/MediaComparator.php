<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Comparator which calculate change set for medias
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class MediaComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(ProductValueInterface $value)
    {
        return in_array($value->getAttribute()->getAttributeType(), ['pim_catalog_file', 'pim_catalog_image']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
    {
        if ($this->hasNewMedia($submittedData) || isset($submittedData['media']['removed'])) {
            return $submittedData;
        }
    }

    /**
     * Whether or not data contain a new media
     *
     * @param array $data
     *
     * @return boolean
     */
    protected function hasNewMedia(array $data)
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
