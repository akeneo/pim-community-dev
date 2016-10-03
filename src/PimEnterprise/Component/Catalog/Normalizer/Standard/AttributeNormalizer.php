<?php

namespace PimEnterprise\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
