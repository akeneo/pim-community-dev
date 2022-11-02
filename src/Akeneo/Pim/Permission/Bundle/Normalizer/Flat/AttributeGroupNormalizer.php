<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Normalizer\Flat;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat attribute group normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AttributeGroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $attrGroupNormalizer;

    /** @var AttributeGroupAccessManager */
    protected $accessManager;

    /**
     * @param NormalizerInterface         $attrGroupNormalizer
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(
        NormalizerInterface $attrGroupNormalizer,
        AttributeGroupAccessManager $accessManager
    ) {
        $this->attrGroupNormalizer = $attrGroupNormalizer;
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroupInterface $attributeGroup
     */
    public function normalize($attributeGroup, $format = null, array $context = [])
    {
        $normalizedAttrGroup = $this->attrGroupNormalizer->normalize($attributeGroup, $format, $context);

        $normalizedAttrGroup['view_permission'] = implode(
            ',',
            array_map('strval', $this->accessManager->getViewUserGroups($attributeGroup))
        );
        $normalizedAttrGroup['edit_permission'] = implode(
            ',',
            array_map('strval', $this->accessManager->getEditUserGroups($attributeGroup))
        );

        return $normalizedAttrGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->attrGroupNormalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->attrGroupNormalizer instanceof CacheableSupportsMethodInterface
            && $this->attrGroupNormalizer->hasCacheableSupportsMethod();
    }
}
