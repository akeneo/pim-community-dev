<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an AttributeInterface entity into array
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    /** @var DateTimeNormalizer */
    protected $dateTimeNormalizer;

    /**
     * @param TranslationNormalizer $translationNormalizer
     * @param DateTimeNormalizer    $dateTimeNormalizer
     */
    public function __construct(TranslationNormalizer $translationNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeInterface $attribute
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        return [
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
            'auto_option_sorting'    => $attribute->getProperty('auto_option_sorting'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && 'standard' === $format;
    }
}
