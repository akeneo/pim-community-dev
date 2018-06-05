<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 */
class AttributeNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $attributeNormalizer;

    /**
     * @param NormalizerInterface $attributeNormalizer
     */
    public function __construct(NormalizerInterface $attributeNormalizer)
    {
        $this->attributeNormalizer = $attributeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        return
            $this->attributeNormalizer->normalize($attribute, $format, $context) +
            ['is_read_only' => (bool) $attribute->getProperty('is_read_only')];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && 'standard' === $format;
    }
}
