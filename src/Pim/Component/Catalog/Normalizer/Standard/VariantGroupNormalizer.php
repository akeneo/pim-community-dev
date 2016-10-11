<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var NormalizerInterface */
    protected $valuesNormalizer;

    /** @var DenormalizerInterface */
    protected $valuesDenormalizer;

    /**
     * @param NormalizerInterface   $translationNormalizer
     * @param NormalizerInterface   $valuesNormalizer
     * @param DenormalizerInterface $valuesDenormalizer
     */
    public function __construct(
        NormalizerInterface $translationNormalizer,
        NormalizerInterface $valuesNormalizer,
        DenormalizerInterface $valuesDenormalizer
    ) {
        $this->translationNormalizer = $translationNormalizer;
        $this->valuesNormalizer = $valuesNormalizer;
        $this->valuesDenormalizer = $valuesDenormalizer;
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
            'labels' => $this->translationNormalizer->normalize($variantGroup, 'standard', $context),
        ];
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

        // As variant group > product template > values data are not type hinted we cannot normalize them directly
        // so we first denormalize them into product values using the common format then normalize them
        // this allow to transform localization based values for example
        return $this->valuesNormalizer->normalize(
            $this->valuesDenormalizer->denormalize(
                $variantGroup->getProductTemplate()->getValuesData(),
                'json',
                []
            ),
            'standard',
            []
        );
    }
}
