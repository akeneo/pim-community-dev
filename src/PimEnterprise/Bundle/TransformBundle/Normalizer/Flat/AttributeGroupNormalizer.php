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
        $this->accessManager       = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeGroup, $format = null, array $context = [])
    {
        $normalizedAttrGroup = $this->attrGroupNormalizer->normalize($attributeGroup, $format, $context);
        if (true === $context['versioning']) {
            $normalizedAttrGroup['view_permission'] = implode(
                array_map('strval', $this->accessManager->getViewUserGroups($attributeGroup)),
                ','
            );
            $normalizedAttrGroup['edit_permission'] = implode(
                array_map('strval', $this->accessManager->getEditUserGroups($attributeGroup)),
                ','
            );
        }

        return $normalizedAttrGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->attrGroupNormalizer->supportsNormalization($data, $format);
    }
}
