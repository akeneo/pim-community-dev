<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Pim\Component\Catalog\Model\ProductMediaInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a media or a file when normalizes a product value as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated since 1.4 should be removed in 1.5
 */
class MediaNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (null === $object->getFilename() && null === $object->getOriginalFilename()) {
            return null;
        }

        return ['filename' => $object->getFilename(), 'originalFilename' => $object->getOriginalFilename()];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductMediaInterface && 'mongodb_json' === $format;
    }
}
