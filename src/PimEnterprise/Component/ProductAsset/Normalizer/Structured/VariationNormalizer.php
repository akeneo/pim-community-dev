<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Normalizer\Structured;

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
    protected $supportedFormats = ['structured'];

    /**
     * {@inheritdoc}
     */
    public function normalize($variation, $format = null, array $context = [])
    {
        $normalizedVariation = [
            'asset'          => $variation->getAsset()->getCode(),
            'locale'         => null,
            'channel'        => null,
            'reference_file' => null,
            'variation_file' => null,
        ];

        if (null !== $variation->getLocale()) {
            $normalizedVariation['locale'] = $variation->getLocale()->getCode();
        }

        if (null !== $variation->getChannel()) {
            $normalizedVariation['channel'] = $variation->getChannel()->getCode();
        }

        if (null !== $variation->getReference() && null !== $variation->getReference()->getFileInfo()) {
            $normalizedVariation['reference_file'] = $variation->getReference()->getFileInfo()->getKey();
        }

        if (null !== $variation->getFileInfo()) {
            $normalizedVariation['variation_file'] = $variation->getFileInfo()->getKey();
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
