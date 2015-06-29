<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for medias
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, ['pim_catalog_file', 'pim_catalog_image']);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => ['filePath' => null]];
        $originals = array_merge($default, $originals);

        if ($this->getHashFile($data['value']['filePath']) === $this->getHashFile($originals['value']['filePath'])) {
            return null;
        }

        $filename = strrchr($data['value']['filePath'], DIRECTORY_SEPARATOR);
        $data['value']['filename'] = str_replace(DIRECTORY_SEPARATOR, '', $filename);

        return $data;
    }

    /**
     * @param string $filePath
     *
     * @return null|string
     */
    protected function getHashFile($filePath = null)
    {
        return null !== $filePath ? sha1_file($filePath) : null;
    }
}
