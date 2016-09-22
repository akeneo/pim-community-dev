<?php

namespace Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($price, $format = null, array $context = [])
    {
        //TODO: when the Price object is loaded from the database, the data is converted to a string
        //TODO: see http://doctrine-orm.readthedocs.io/projects/doctrine-dbal/en/latest/reference/types.html#decimal
        //TODO: at this point, $price->getData() = '45.32165' or '56.000000'

        return [
            'data'     => $price->getData(),
            'currency' => $price->getCurrency(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPriceInterface && 'standard' === $format;
    }
}
