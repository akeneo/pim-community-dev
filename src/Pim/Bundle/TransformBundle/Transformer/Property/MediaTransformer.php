<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Media attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTransformer implements PropertyTransformerInterface, EntityUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $value = trim($value);

        if (empty($value) || is_dir($value)) {
            return;
        }

        try {
            $file = new File($value);
        } catch (FileNotFoundException $e) {
            throw new PropertyTransformerException('File not found: "%value%"', array('%value%' => $value));
        }

        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        if (null === $data) {
            return;
        }

        $media = $object->getMedia();
        if (!$media) {
            $media = new Media();
            $object->setMedia($media);
        }
        $media->setFile($data);
    }
}
