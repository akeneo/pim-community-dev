<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
    public function normalize($associationType, $format = null, array $context = [])
    {
        $normalizedAssociationType = $this->stdNormalizer->normalize($associationType, 'standard', $context);

        if (empty($normalizedAssociationType['labels'])) {
            $normalizedAssociationType['labels'] = (object) $normalizedAssociationType['labels'];
        }

        return $normalizedAssociationType;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AssociationTypeInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
