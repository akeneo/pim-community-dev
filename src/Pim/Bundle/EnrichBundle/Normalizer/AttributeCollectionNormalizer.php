<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Attribute collection normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCollectionNormalizer implements NormalizerInterface, FilterableNormalizerInterface
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
    public function normalize($attributes, $format = null, array $context = array())
    {
        $normalizedAttributes = [];
        foreach ($attributes as $attribute) {
            $normalizedAttributes[] = $this->attributeNormalizer->normalize($attribute, 'json', $context);
        }

        return $normalizedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }
}
