<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Normalizer\Authorization;

use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product to get identifier and data needed to know if resource is granted or not.
 */
class ProductNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        return [
            'identifier' => $product->getIdentifier(),
            'categories' => $product->getCategoryCodes(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'authorization' === $format;
    }
}
