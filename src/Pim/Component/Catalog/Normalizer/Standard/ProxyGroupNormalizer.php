<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Group normalizer proxy that calls the GroupNormalizer or VariantGroupNormalizer depending on the group to normalize
 * and the value returned by GroupInterface::isVariantGroup function.
 *
 * The problem is the symfony serializer internally uses a cache to keep track of the normalizers that supports the
 * class of the objects to normalize.
 *
 * Given this context, if the supportsNormalization function calls the GroupInterface::isVariant function to dynamically
 * indicate whether the object is supported, it can mislead the serializer. For instance the normalizer can tell the
 * serializer that it does not support the objects of class GroupInterface because the supportsNormalization function
 * was called with a variant group (isVariant = false).
 *
 * This class is meant to be deleted when a proper VariantGroupInterface is implemented in the PIM.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProxyGroupNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['standard'];

    /** @var NormalizerInterface */
    protected $groupNormalizer;

    /**
     * @param NormalizerInterface $groupNormalizer
     * @param NormalizerInterface $variantGroupNormalizer
     */
    public function __construct(
        NormalizerInterface $groupNormalizer
    ) {
        $this->groupNormalizer = $groupNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param GroupInterface $object
     *
     * @return array
     */
    public function normalize($group, $format = null, array $context = [])
    {
        return $this->groupNormalizer->normalize($group, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }
}
