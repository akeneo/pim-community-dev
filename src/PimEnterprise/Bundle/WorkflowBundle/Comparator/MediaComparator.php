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
        $default = ['locale' => null, 'scope' => null, 'value' => ['filePath' => null]];
        $originals = array_merge($default, $originals);

        if ($this->getHashFile($data['value']['filePath']) === $this->getHashFile($originals['value']['filePath'])) {
            return null;
        }

        $filename = strrchr($data['value']['filePath'], self::SEPATATOR_FILE);
        $data['value']['filename'] = str_replace(self::SEPATATOR_FILE, '', $filename);

        return $data;
    }

    /**
     * @param string $filePath
     *
     * @return null|string
     */
    protected function getHashFile($filePath = null)
    {
        if (null !== $filePath) {
            return sha1_file($filePath);
        }

        return null;
    }
}
