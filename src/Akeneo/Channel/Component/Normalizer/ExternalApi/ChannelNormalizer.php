<?php

namespace Akeneo\Channel\Component\Normalizer\ExternalApi;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
    public function normalize($channel, $format = null, array $context = [])
    {
        $normalizedChannel = $this->stdNormalizer->normalize($channel, 'standard', $context);

        if (empty($normalizedChannel['labels'])) {
            $normalizedChannel['labels'] = (object) $normalizedChannel['labels'];
        }

        if (empty($normalizedChannel['conversion_units'])) {
            $normalizedChannel['conversion_units'] = (object) $normalizedChannel['conversion_units'];
        }

        return $normalizedChannel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ChannelInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
