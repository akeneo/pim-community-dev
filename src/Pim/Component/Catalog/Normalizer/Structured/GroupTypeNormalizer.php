<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a group type
 *
 * @author    Marie Minasyan <marie.minasyan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string[] */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($groupType, $format = null, array $context = [])
    {
        return [
            'code'       => $groupType->getCode(),
            'is_variant' => $groupType->isVariant(),
        ] + $this->normalizer->normalize($groupType, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupTypeInterface && in_array($format, $this->supportedFormats);
    }
}
