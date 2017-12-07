<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Api\Normalizer;

use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetReferenceNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($reference, $format = null, array $context = []): array
    {
        $localeCode = $reference->getLocale() ? $reference->getLocale()->getCode() : null;

        if (null === $reference->getFileInfo()) {
            return [
                'locale' => $localeCode,
                'code' => null,
            ];
        }

        $code = $reference->getFileInfo()->getKey();

        return [
            'locale' => $localeCode,
            'code' => $code,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ReferenceInterface && 'external_api' === $format;
    }
}
