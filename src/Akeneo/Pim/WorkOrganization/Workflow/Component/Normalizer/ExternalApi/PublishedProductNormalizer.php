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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalized.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected NormalizerInterface $productNormalizer;
    private NormalizerInterface $associationNormalizer;

    public function __construct(NormalizerInterface $productNormalizer, NormalizerInterface $associationNormalizer)
    {
        $this->productNormalizer = $productNormalizer;
        $this->associationNormalizer = $associationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $normalizedPublishedProduct = $this->productNormalizer->normalize($product, $format, $context);

        // TODO: PIM-6564 will be done when we'll publish product model
        if (array_key_exists('parent', $normalizedPublishedProduct)) {
            unset($normalizedPublishedProduct['parent']);
        }
        if (array_key_exists('with_quality_scores', $context)) {
            $normalizedPublishedProduct['quality_scores'] = [];
        }

        $normalizedPublishedProduct['associations'] = $this->associationNormalizer->normalize(
            $product,
            $format,
            $context
        );

        return $normalizedPublishedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PublishedProductInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
