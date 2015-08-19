<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Normalizer;

use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        $normalizedProduct = $this->normalizer->normalize($publishedProduct, 'json', $context);

        $normalizedProduct['meta'] = array_merge(
            $normalizedProduct['meta'],
            [
                'original_product_id' => $publishedProduct->getOriginalProduct()->getId()
            ]
        );

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PublishedProductInterface && in_array($format, $this->supportedFormat);
    }
}
