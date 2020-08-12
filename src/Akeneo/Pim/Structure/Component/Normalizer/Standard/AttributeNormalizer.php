<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an AttributeInterface entity into array
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $translationNormalizer;

    /** @var NormalizerInterface */
    private $dateTimeNormalizer;

    /** @var array */
    private $properties;

    /**
     * @param NormalizerInterface $translationNormalizer
     * @param NormalizerInterface $dateTimeNormalizer
     * @param array               $properties
     */
    public function __construct(
        NormalizerInterface $translationNormalizer,
        NormalizerInterface $dateTimeNormalizer,
        array $properties
    ) {
        $this->translationNormalizer = $translationNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeInterface $attribute
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedProperties = [];
        foreach ($this->properties as $property) {
            $normalizedProperties[$property] = $attribute->getProperty($property);
        }

        $normalizedAttribute = [
            'code'                   => $attribute->getCode(),
            'type'                   => $attribute->getType(),
            'group'                  => ($attribute->getGroup()) ? $attribute->getGroup()->getCode() : null,
            'unique'                 => (bool) $attribute->isUnique(),
            'useable_as_grid_filter' => (bool) $attribute->isUseableAsGridFilter(),
            'allowed_extensions'     => $attribute->getAllowedExtensions(),
            'metric_family'          => '' === $attribute->getMetricFamily() ? null : $attribute->getMetricFamily(),
            'default_metric_unit'    => '' === $attribute->getDefaultMetricUnit() ?
                null : $attribute->getDefaultMetricUnit(),
            'reference_data_name'    => $attribute->getReferenceDataName(),
            'available_locales'      => $attribute->getAvailableLocaleCodes(),
            'max_characters'         => null === $attribute->getMaxCharacters() ?
                null : (int) $attribute->getMaxCharacters(),
            'validation_rule'        => '' === $attribute->getValidationRule() ? null : $attribute->getValidationRule(),
            'validation_regexp'      => '' === $attribute->getValidationRegexp() ?
                null : $attribute->getValidationRegexp(),
            'wysiwyg_enabled'        => $attribute->isWysiwygEnabled(),
            'number_min'             => null === $attribute->getNumberMin() ?
                null : (string) $attribute->getNumberMin(),
            'number_max'             => null === $attribute->getNumberMax() ?
                null : (string) $attribute->getNumberMax(),
            'decimals_allowed'       => $attribute->isDecimalsAllowed(),
            'negative_allowed'       => $attribute->isNegativeAllowed(),
            'date_min'               => $this->dateTimeNormalizer->normalize($attribute->getDateMin()),
            'date_max'               => $this->dateTimeNormalizer->normalize($attribute->getDateMax()),
            'max_file_size'          => null === $attribute->getMaxFileSize() ?
                null : (string) $attribute->getMaxFileSize(),
            'minimum_input_length'   => null === $attribute->getMinimumInputLength() ?
                null : (int) $attribute->getMinimumInputLength(),
            'sort_order'             => (int) $attribute->getSortOrder(),
            'localizable'            => (bool) $attribute->isLocalizable(),
            'scopable'               => (bool) $attribute->isScopable(),
            'labels'                 => $this->translationNormalizer->normalize($attribute, $format, $context),
        ];

        return $normalizedAttribute + $normalizedProperties;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
