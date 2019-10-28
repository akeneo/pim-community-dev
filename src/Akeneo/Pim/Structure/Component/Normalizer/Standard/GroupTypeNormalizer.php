<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /**
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(NormalizerInterface $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($groupType, $format = null, array $context = [])
    {
        return [
            'code'       => $groupType->getCode(),
            'labels'     => $this->translationNormalizer->normalize($groupType, 'standard', $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupTypeInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
