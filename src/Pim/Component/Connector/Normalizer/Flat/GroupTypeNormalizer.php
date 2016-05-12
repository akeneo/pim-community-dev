<?php

namespace Pim\Component\Connector\Normalizer\Flat;

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
    protected $transNormalizer;

    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * @param NormalizerInterface $transNormalizer
     */
    public function __construct(NormalizerInterface $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($groupType, $format = null, array $context = [])
    {
        return [
            'code'       => $groupType->getCode(),
            'is_variant' => (int) $groupType->isVariant(),
        ] + $this->transNormalizer->normalize($groupType, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupTypeInterface && in_array($format, $this->supportedFormats);
    }
}
