<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface
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
    public function normalize($category, $format = null, array $context = [])
    {
        return [
            'code'   => $category->getCode(),
            'parent' => null !== $category->getParent() ? $category->getParent()->getCode() : null,
            'labels' => $this->translationNormalizer->normalize($category, 'standard', $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && 'standard' === $format;
    }
}
