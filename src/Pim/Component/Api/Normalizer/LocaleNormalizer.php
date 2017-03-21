<?php

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Catalog\Model\LocaleInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleNormalizer implements NormalizerInterface
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
    public function normalize($locale, $format = null, array $context = [])
    {
        return $this->stdNormalizer->normalize($locale, 'standard', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LocaleInterface && 'external_api' === $format;
    }
}
