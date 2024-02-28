<?php

namespace Akeneo\Category\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    private DateTimeNormalizer $dateTimeNormalizer;

    public function __construct(TranslationNormalizer $translationNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    public function normalize($category, $format = null, array $context = [])
    {
        return [
            'code' => $category->getCode(),
            'parent' => null !== $category->getParent() ? $category->getParent()->getCode() : null,
            'updated' => $this->dateTimeNormalizer->normalize($category->getUpdated(), $format),
            'labels' => $this->translationNormalizer->normalize($category, 'standard', $context),
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof CategoryInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
