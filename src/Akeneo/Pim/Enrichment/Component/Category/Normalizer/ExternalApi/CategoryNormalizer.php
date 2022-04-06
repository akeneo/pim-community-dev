<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Category\Manager\PositionResolverInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected NormalizerInterface $stdNormalizer;
    protected PositionResolverInterface $positionResolver;

    public function __construct(NormalizerInterface $stdNormalizer, PositionResolverInterface $positionResolver)
    {
        $this->stdNormalizer = $stdNormalizer;
        $this->positionResolver = $positionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $normalizedCategory = $this->stdNormalizer->normalize($category, 'standard', $context);

        if (empty($normalizedCategory['labels'])) {
            $normalizedCategory['labels'] = (object) $normalizedCategory['labels'];
        }

        if (in_array('with_position', $context)) {
            $normalizedCategory['position'] = $this->positionResolver->getPosition($category);
            $normalizedCategory['level'] = $category->getLevel() + 1;
        }

        return $normalizedCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
