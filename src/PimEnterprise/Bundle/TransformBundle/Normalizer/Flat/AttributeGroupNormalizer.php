<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TransformBundle\Normalizer\Flat;

use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat attribute group normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $attributeGroupNormalizer;

    /** @var AttributeGroupAccessManager */
    protected $accessManager;

    /**
     * @param NormalizerInterface         $attributeGroupNormalizer
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(
        NormalizerInterface $attributeGroupNormalizer,
        AttributeGroupAccessManager $accessManager
    ) {
        $this->attributeGroupNormalizer = $attributeGroupNormalizer;
        $this->accessManager            = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeGroup, $format = null, array $context = [])
    {
        $normalizedAttributeGroup = $this->attributeGroupNormalizer->normalize($attributeGroup, $format, $context);

        if (true === $context['versioning']) {
            $normalizedAttributeGroup['view_permission'] = implode(
                array_map('strval', $this->accessManager->getViewUserGroups($attributeGroup)),
                ','
            );
            $normalizedAttributeGroup['edit_permission'] = implode(
                array_map('strval', $this->accessManager->getEditUserGroups($attributeGroup)),
                ','
            );
        }

        return $normalizedAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->attributeGroupNormalizer->supportsNormalization($data, $format);
    }
}
