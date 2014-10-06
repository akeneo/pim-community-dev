<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a media or a file when normalizes a product value as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return ['filename' => $object->getFilename(), 'originalFilename' => $object->getOriginalFilename()];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractProductMedia && 'mongodb_json' === $format;
    }
}
