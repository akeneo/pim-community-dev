<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric entity into an array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const DECIMAL_PRECISION = 4;

    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = [])
    {
        $amount = $metric->getData();

        // if decimals_allowed is false, we return an integer
        // if true, we return a string to avoid to loose precision (http://floating-point-gui.de)
        if (null !== $amount && is_numeric($amount) && isset($context['is_decimals_allowed'])) {
            $amount = $context['is_decimals_allowed']
                ? number_format($amount, static::DECIMAL_PRECISION, '.', '') : (int) $amount;
        }

        if (null === $amount) {
            return [
                'amount' => null,
                'unit'   => null,
            ];
        }

        return [
            'amount' => $amount,
            'unit'   => $metric->getUnit()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MetricInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
