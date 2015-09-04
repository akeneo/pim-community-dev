<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Normalizer\Flat;

use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a variation
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class VariationNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     */
    public function normalize($variation, $format = null, array $context = [])
    {
        $normalizedVariation['asset']   = $variation->getAsset()->getCode();
        $normalizedVariation['locale']  = null !== $variation->getLocale() ? $variation->getLocale()->getCode() : '';
        $normalizedVariation['channel'] = null !== $variation->getChannel() ? $variation->getChannel()->getCode() : '';

        if (null !== $variation->getReference() && null !== $variation->getReference()->getFileInfo()) {
            $normalizedVariation['reference_file'] = $variation->getReference()->getFileInfo()->getKey();
        } else {
            $normalizedVariation['reference_file'] = '';
        }

        if (null !== $variation->getFileInfo()) {
            $normalizedVariation['variation_file'] = $variation->getFileInfo()->getKey();
        } else {
            $normalizedVariation['variation_file'] = '';
        }

        return $normalizedVariation;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof VariationInterface && in_array($format, $this->supportedFormats);
    }
}
