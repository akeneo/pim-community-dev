<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\Value\MetricValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize the Metric values for the Datagrid.
 * The translation is font font-end side.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($metric, $format = null, array $context = array()): array
    {
        return [
            'data' => [
                'unit' => $metric->getUnit(),
                'amount' => $metric->getAmount(),
                'family' => $metric->getData()->getFamily(),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return 'datagrid' === $format && $data instanceof MetricValueInterface;
    }
}
