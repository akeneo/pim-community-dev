<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
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
     * @param GroupInterface $group
     *
     * @return array
     */
    public function normalize($group, $format = null, array $context = [])
    {
        $standardGroup = $this->standardNormalizer->normalize($group, 'standard', $context);
        $flatGroup = $standardGroup;

        unset($flatGroup['labels']);
        $flatGroup += $this->translationNormalizer->normalize($standardGroup['labels'], 'flat', $context);

        return $flatGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
