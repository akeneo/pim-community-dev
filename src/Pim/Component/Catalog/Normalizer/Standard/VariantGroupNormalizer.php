<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupNormalizer implements NormalizerInterface
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
    public function normalize($variantGroup, $format = null, array $context = [])
    {
        return [
            'code'   => $variantGroup->getCode(),
            'type'   => $variantGroup->getType()->getCode(),
            'axes'   => $this->normalizeAxesAttributes($variantGroup),
            'values' => $this->normalizeVariantGroupValues($variantGroup),
        ] + $this->translationNormalizer->normalize($variantGroup, 'standard', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface && $data->getType()->isVariant() && 'standard' === $format;
    }

    /**
     * Normalize the attributes
     *
     * @param GroupInterface $variantGroup
     *
     * @return array
     */
    protected function normalizeAxesAttributes(GroupInterface $variantGroup)
    {
        $attributes = [];
        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            $attributes[] = $attribute->getCode();
        }
        sort($attributes);

        return $attributes;
    }

    /**
     * Normalize the variant group values
     *
     * @param GroupInterface $variantGroup
     *
     * @return array
     */
    protected function normalizeVariantGroupValues(GroupInterface $variantGroup)
    {
        if (null === $template = $variantGroup->getProductTemplate()) {
            return [];
        }

        return $template->getValuesData();
    }
}
