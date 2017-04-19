<?php

namespace Pim\Bundle\DataGridBundle\Normalizer;

/**
 * Normalize Products for variant group grid
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantGroupProductNormalizer extends ProductNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = array())
    {
        $data = parent::normalize($product, $format, $context);

        $data['in_group'] = null !== $product->getVariantGroup();
        $data['is_checked'] = null !== $product->getVariantGroup();

        return $data;
    }
}
