<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\AttributeGroupNormalizer as StandardNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Flat attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    /** @var CategoryNormalizer */
    protected $standardNormalizer;

    /**
     * @param NormalizerInterface   $standardNormalizer
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroupInterface $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $standardAttributeGroup = $this->standardNormalizer->normalize($object, 'standard', $context);
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
}
