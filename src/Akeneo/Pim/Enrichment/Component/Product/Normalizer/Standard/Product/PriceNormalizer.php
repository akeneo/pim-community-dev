<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const DECIMAL_PRECISION = 2;

    /**
     * {@inheritdoc}
     */
    public function normalize($price, $format = null, array $context = [])
    {
        $amount = $price->getData();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (null !== $amount && is_numeric($amount) && isset($context['is_decimals_allowed'])) {
            $amount = $context['is_decimals_allowed']
                ? number_format($amount, static::DECIMAL_PRECISION, '.', '') : (int) $amount;
        }

        return [
            'amount'   => $amount,
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
