<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    /**
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(TranslationNormalizer $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = [])
    {
        return [
            'code'   => $group->getCode(),
            'type'   => $group->getType()->getCode(),
            'labels' => $this->translationNormalizer->normalize($group, 'standard', $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
