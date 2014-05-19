<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * Normalize/Denormalize media product value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MediaNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @staticvar string */
    const FORMAT = 'proposal';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'filename' => $object->getFilename(),
            'filePath' => $object->getFilePath(),
            'originalFilename' => $object->getOriginalFilename(),
            'mimeType' => $object->getMimeType(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        // TODO (2014-05-15 15:05 by Gildas): $context['instance'] must be null (?) or a Media instance
        $media = $context['instance'] ?: new Media();

        if (isset($data['filename'])) {
            $media->setFilename($data['filename']);
        }

        if (isset($data['filePath'])) {
            $media->setFilePath($data['filePath']);
        }

        if (isset($data['originalFilename'])) {
            $media->setOriginalFilename($data['originalFilename']);
        }

        if (isset($data['mimeType'])) {
            $media->setMimeType($data['mimeType']);
        }

        return $media;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Media && self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, ['pim_catalog_file', 'pim_catalog_image']) && self::FORMAT === $format;
    }
}
