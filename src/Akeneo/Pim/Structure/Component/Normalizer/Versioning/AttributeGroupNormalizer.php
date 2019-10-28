<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroupInterface $attributeGroup
     *
     * @return array
     */
    public function normalize($attributeGroup, $format = null, array $context = [])
    {
        $standardAttributeGroup = $this->standardNormalizer->normalize($attributeGroup, 'standard', $context);
        $flatAttributeGroup = $standardAttributeGroup;

        $flatAttributeGroup['attributes'] = implode(self::ITEM_SEPARATOR, $standardAttributeGroup['attributes']);

        unset($flatAttributeGroup['labels']);
        $flatAttributeGroup += $this->translationNormalizer->normalize(
            $standardAttributeGroup['labels'],
            'flat',
            $context
        );

        return $flatAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroupInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
