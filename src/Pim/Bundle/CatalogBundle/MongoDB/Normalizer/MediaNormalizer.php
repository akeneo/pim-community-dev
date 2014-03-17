<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\Media;

/**
 * Skip the normalization of a media or a file when normalize a product value as mongodb_json
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
        return ['filename' => $object->getFilename()];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Media && 'mongodb_json' === $format;
    }
}
