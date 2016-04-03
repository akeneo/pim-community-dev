<?php

namespace Pim\Component\Catalog\Normalizer\Structured;

use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform an AttributeInterface entity into array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    const LOCALIZABLE_PATTERN = '{locale}:{value}';
    const ITEM_SEPARATOR      = ',';
    const GROUP_SEPARATOR     = '|';
    const GLOBAL_SCOPE        = 'Global';
    const CHANNEL_SCOPE       = 'Channel';
    const ALL_LOCALES         = 'All';

    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var TranslationNormalizer */
    protected $transNormalizer;

    /**
     * Constructor
     *
     * @param TranslationNormalizer $transNormalizer
     */
    public function __construct(TranslationNormalizer $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $results = [
            'type' => $object->getAttributeType(),
            'code' => $object->getCode(),
            ] + $this->transNormalizer->normalize($object, $format, $context);

        $results = array_merge(
            $results,
            [
                'group'                   => ($object->getGroup()) ? $object->getGroup()->getCode() : null,
                'unique'                  => (int) $object->isUnique(),
                'useable_as_grid_filter'  => (int) $object->isUseableAsGridFilter(),
                'allowed_extensions'      => implode(self::ITEM_SEPARATOR, $object->getAllowedExtensions()),
                'metric_family'           => $object->getMetricFamily(),
                'default_metric_unit'     => $object->getDefaultMetricUnit(),
                'reference_data_name'     => $object->getReferenceDataName(),
                'available_locales'       => $this->normalizeAvailableLocales($object),
                'max_characters'          => (string) $object->getMaxCharacters(),
                'validation_rule'         => (string) $object->getValidationRule(),
                'validation_regexp'       => (string) $object->getValidationRegexp(),
                'wysiwyg_enabled'         => (string) $object->isWysiwygEnabled(),
                'number_min'              => (string) $object->getNumberMin(),
                'number_max'              => (string) $object->getNumberMax(),
                'decimals_allowed'        => (string) $object->isDecimalsAllowed(),
                'negative_allowed'        => (string) $object->isNegativeAllowed(),
                'date_min'                => $this->normalizeDate($object->getDateMin()),
                'date_max'                => $this->normalizeDate($object->getDateMax()),
                'max_file_size'           => (string) $object->getMaxFileSize(),
                'minimum_input_length'    => (string) $object->getMinimumInputLength(),
            ]
        );
        if (isset($context['versioning'])) {
            $results = array_merge($results, $this->getVersionedData($object));
        } else {
            $results = array_merge(
                $results,
                [
                    'localizable' => (int) $object->isLocalizable(),
                    'scopable'    => (int) $object->isScopable(),
                ]
            );
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Get extra data to store in version
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getVersionedData(AttributeInterface $attribute)
    {
        return [
            'available_locales'   => $this->normalizeAvailableLocales($attribute),
            'localizable'         => $attribute->isLocalizable(),
            'scope'               => $attribute->isScopable() ? self::CHANNEL_SCOPE : self::GLOBAL_SCOPE,
            'options'             => $this->normalizeOptions($attribute),
            'sort_order'          => (int) $attribute->getSortOrder(),
            'required'            => (int) $attribute->isRequired(),
            'max_characters'      => (string) $attribute->getMaxCharacters(),
            'validation_rule'     => (string) $attribute->getValidationRule(),
            'validation_regexp'   => (string) $attribute->getValidationRegexp(),
            'wysiwyg_enabled'     => (string) $attribute->isWysiwygEnabled(),
            'number_min'          => (string) $attribute->getNumberMin(),
            'number_max'          => (string) $attribute->getNumberMax(),
            'decimals_allowed'    => (string) $attribute->isDecimalsAllowed(),
            'negative_allowed'    => (string) $attribute->isNegativeAllowed(),
            'date_min'            => $this->normalizeDate($attribute->getDateMin()),
            'date_max'            => $this->normalizeDate($attribute->getDateMax()),
            'metric_family'       => (string) $attribute->getMetricFamily(),
            'default_metric_unit' => (string) $attribute->getDefaultMetricUnit(),
            'max_file_size'       => (string) $attribute->getMaxFileSize(),
        ];
    }

    /**
     * Normalize available locales
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function normalizeAvailableLocales(AttributeInterface $attribute)
    {
        $locales = $attribute->getLocaleSpecificCodes();

        return $locales;
    }

    /**
     * Normalize options
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function normalizeOptions(AttributeInterface $attribute)
    {
        $data = [];
        $options = $attribute->getOptions();
        foreach ($options as $option) {
            $data[$option->getCode()] = [];
            foreach ($option->getOptionValues() as $value) {
                $data[$option->getCode()][$value->getLocale()] = $value->getValue();
            }
        }

        return $data;
    }

    /**
     * Normalize date property
     * 
     * @param \DateTime|null when null returns '' (empty string)
     * 
     * @return string 
     */
    protected function normalizeDate($date = null)
    {
        if (!is_null($date) && $date instanceof \DateTime) {
            return $date->format(\DateTime::ISO8601);
        }

        return '';
    }
}
