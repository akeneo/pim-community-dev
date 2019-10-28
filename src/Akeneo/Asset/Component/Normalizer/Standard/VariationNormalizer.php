<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Normalizer\Standard;

use Akeneo\Asset\Component\Model\VariationInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class VariationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($variation, $format = null, array $context = [])
    {
        $normalizedVariation = [
            'asset'          => $variation->getAsset()->getCode(),
            'code'           => null,
            'locale'         => null,
            'channel'        => null,
            'reference_file' => null,
        ];

        if (null !== $variation->getFileInfo()) {
            $normalizedVariation['code'] = $variation->getFileInfo()->getKey();
        }

        if (null !== $variation->getLocale()) {
            $normalizedVariation['locale'] = $variation->getLocale()->getCode();
        }

        if (null !== $variation->getChannel()) {
            $normalizedVariation['channel'] = $variation->getChannel()->getCode();
        }

        if (null !== $variation->getReference() && null !== $variation->getReference()->getFileInfo()) {
            $normalizedVariation['reference_file'] = $variation->getReference()->getFileInfo()->getKey();
        }

        return $normalizedVariation;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof VariationInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
