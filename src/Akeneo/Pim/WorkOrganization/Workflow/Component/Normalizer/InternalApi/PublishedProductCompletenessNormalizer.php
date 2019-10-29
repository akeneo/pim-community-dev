<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class PublishedProductCompletenessNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProductCompleteness, $format = null, array $context = []): array
    {
        return [
            'required' => $publishedProductCompleteness->requiredCount(),
            'missing' => count($publishedProductCompleteness->missingAttributeCodes()),
            'ratio' => $publishedProductCompleteness->ratio(),
            'locale' => $publishedProductCompleteness->localeCode(),
            'channel' => $publishedProductCompleteness->channelCode(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PublishedProductCompleteness && 'internal_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
