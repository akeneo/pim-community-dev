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

use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalized.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $productNormalizer;

    /**
     * @param NormalizerInterface $productNormalizer
     */
    public function __construct(NormalizerInterface $productNormalizer)
    {
        $this->productNormalizer = $productNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $normalizedPublishedProduct = $this->productNormalizer->normalize($product, $format, $context);

        if (array_key_exists('variant_group', $normalizedPublishedProduct)) {
            unset($normalizedPublishedProduct['variant_group']);
        }

        // TODO: PIM-6564 will be done when we'll publish product model
        if (array_key_exists('parent', $normalizedPublishedProduct)) {
            unset($normalizedPublishedProduct['parent']);
        }

        return $normalizedPublishedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PublishedProductInterface && 'external_api' === $format;
    }
}
