<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Normalizer\Structured;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an AttributeInterface entity into array
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
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
    public function normalize($object, $format = null, array $context = [])
    {
        return
            $this->attributeNormalizer->normalize($object, $format, $context) +
            ['is_read_only' => (bool) $object->getProperty('is_read_only')];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->attributeNormalizer->supportsNormalization($data, $format);
    }
}
