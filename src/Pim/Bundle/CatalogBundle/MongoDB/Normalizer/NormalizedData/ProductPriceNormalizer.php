<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price collection to store it as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPriceNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = null;
        if ($object->getData() !== null) {
            $data = [
                'data'     => $object->getData(),
                'currency' => $object->getCurrency()
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPriceInterface && 'mongodb_json' === $format;
    }
}
