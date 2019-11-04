<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**  @var string[] */
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
     * @param CategoryInterface $category
     *
     * @return array
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $standardCategory = $this->standardNormalizer->normalize($category, 'standard', $context);
        $flatCategory = $standardCategory;

        unset($flatCategory['labels']);
        $flatCategory += $this->translationNormalizer->normalize($standardCategory['labels'], 'flat', $context);

        return $flatCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
