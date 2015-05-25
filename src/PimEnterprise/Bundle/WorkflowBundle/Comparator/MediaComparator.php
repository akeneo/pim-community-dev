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

/**
 * Comparator which calculate change set for medias
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class MediaComparator implements ComparatorInterface
{
    /** @staticvar string */
    const SEPATATOR_FILE = '/';

    /**
     * {@inheritdoc}
     */
    public function supportsComparison($type)
    {
        return in_array($type, ['pim_catalog_file', 'pim_catalog_image']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
    {
        $noValue = !isset($originals['value']) && !isset($data['value']) && !isset($originals['value']['filePath']);
        $noFilepathChange = $data['value']['filePath'] === $originals['value']['filePath'];

        if ($noValue || $noFilepathChange) {
            return null;
        }

        $filename = strrchr($data['value']['filePath'], self::SEPATATOR_FILE);
        $data['value']['filename'] = str_replace(self::SEPATATOR_FILE, '', $filename);

        return $data;
    }
}
