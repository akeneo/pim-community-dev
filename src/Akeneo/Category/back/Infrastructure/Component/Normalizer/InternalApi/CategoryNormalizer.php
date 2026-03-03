<?php

namespace Akeneo\Category\Infrastructure\Component\Normalizer\InternalApi;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $categoryNormalizer;

    public function __construct(NormalizerInterface $categoryNormalizer)
    {
        $this->categoryNormalizer = $categoryNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $standardCategory = $this->categoryNormalizer->normalize($category, 'standard', $context);

        $standardCategory['id'] = $category->getId();
        if ($context['with_root'] ?? false) {
            $standardCategory['root'] = $category->getRoot();
        }

        return $standardCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
