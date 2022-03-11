<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected TranslationNormalizer $translationNormalizer;
    private DateTimeNormalizer $dateTimeNormalizer;
    private ApiResourceRepositoryInterface $repository;

    public function __construct(
        TranslationNormalizer $translationNormalizer,
        DateTimeNormalizer $dateTimeNormalizer,
        ApiResourceRepositoryInterface $repository
    ) {
        $this->translationNormalizer = $translationNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $rootCategory = $this->repository->find($category->getRoot());

        return [
            'code' => $category->getCode(),
            'root' => null !== $rootCategory ? $rootCategory->getCode() : null,
            'parent' => null !== $category->getParent() ? $category->getParent()->getCode() : null,
            'updated' => $this->dateTimeNormalizer->normalize($category->getUpdated(), $format),
            'labels' => $this->translationNormalizer->normalize($category, 'standard', $context),
            'nested_tree_node' => [
                'depth' => $category->getLevel(),
                'left' => $category->getLeft(),
                'right' => $category->getRight(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
