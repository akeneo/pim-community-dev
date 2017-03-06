<?php

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /**
     * @param NormalizerInterface $stdNormalizer
     */
    public function __construct(NormalizerInterface $stdNormalizer)
    {
        $this->stdNormalizer = $stdNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        return $this->stdNormalizer->normalize($attribute, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && 'external_api' === $format;
    }
}
