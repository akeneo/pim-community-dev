<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * A normalizer to transform a ProductAttribute entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
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

    protected $supportedFormats = array('csv');

    private $results;

    /**
     * Transforms an object into a flat array
     *
     * @param ProductAttribute $attribute
     * @param string           $format
     * @param array            $context
     *
     * @return array
     */
    public function normalize($attribute, $format = null, array $context = array())
    {
        $attributeTypes = explode('_', $attribute->getAttributeType());

        $dateMin = (is_null($attribute->getDateMin())) ? '' : $attribute->getDateMin()->format(\DateTime::ISO8601);
        $dateMax = (is_null($attribute->getDateMax())) ? '' : $attribute->getDateMax()->format(\DateTime::ISO8601);

        $this->results = array(
            'type'                    => end($attributeTypes),
            'code'                    => $attribute->getCode(),
            'description'             => $attribute->getDescription(),
            'group'                   => $attribute->getVirtualGroup()->getCode(),
            'sort_order'              => $attribute->getSortOrder(),
            'required'                => $attribute->getRequired(),
            'unique'                  => $attribute->getUnique(),
            'searchable'              => $attribute->getSearchable(),
            'localizable'             => $attribute->getTranslatable(),
            'scope'                   => $attribute->getScopable() ? self::CHANNEL_SCOPE : self::GLOBAL_SCOPE,
            'useable_as_grid_column'  => (string) (int) $attribute->isUseableAsGridColumn(),
            'useable_as_grid_filter'  => (string) (int) $attribute->isUseableAsGridFilter(),
            'value_creation_allowed'  => (string) $attribute->isValueCreationAllowed(),
            'default_value'           => (string) $attribute->getDefaultValue(),
            'max_characters'          => (string) $attribute->getMaxCharacters(),
            'validation_rule'         => (string) $attribute->getValidationRule(),
            'validation_regexp'       => (string) $attribute->getValidationRegexp(),
            'wysiwyg_enabled'         => (string) $attribute->isWysiwygEnabled(),
            'number_min'              => (string) $attribute->getNumberMin(),
            'number_max'              => (string) $attribute->getNumberMax(),
            'decimals_allowed'        => (string) $attribute->isDecimalsAllowed(),
            'negative_allowed'        => (string) $attribute->isNegativeAllowed(),
            'date_min'                => $dateMin,
            'date_max'                => $dateMax,
            'date_type'               => (string) $attribute->getDateType(),
            'metric_family'           => (string) $attribute->getMetricFamily(),
            'default_metric_unit'     => (string) $attribute->getDefaultMetricUnit(),
            'allowed_file_sources'    => (string) $attribute->getAllowedFileSources(),
            'allowed_extensions' => implode(self::ITEM_SEPARATOR, $attribute->getAllowedExtensions()),
            'max_file_size'           => (string) $attribute->getMaxFileSize(),
        );

        $this->normalizeLabel($attribute);
        $this->normalizeAvailableLocales($attribute);
        $this->normalizeOptions($attribute);
        $this->normalizeDefaultOptions($attribute);

        return $this->results;
    }

    /**
     * Indicates whether this normalizer can normalize the given data
     *
     * @param mixed  $data
     * @param string $format
     *
     * @return boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductAttribute && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the label
     *
     * @param ProductAttribute $attribute
     *
     * @return void
     */
    protected function normalizeLabel(ProductAttribute $attribute)
    {
        $pattern = self::LOCALIZABLE_PATTERN;
        $labels = $attribute->getTranslations()->map(
            function ($translation) use ($pattern) {
                $label = str_replace('{locale}', $translation->getLocale(), $pattern);
                $label = str_replace('{value}', $translation->getLabel(), $label);

                return $label;
            }
        )->toArray();

        $this->results['label'] = implode(self::ITEM_SEPARATOR, $labels);
    }

    /**
     * Normalize available locales
     *
     * @param ProductAttribute $attribute
     *
     * @return void
     */
    protected function normalizeAvailableLocales($attribute)
    {
        $availableLocales = $attribute->getAvailableLocales();

        if ($availableLocales) {
            $availableLocales = $availableLocales->map(
                function ($locale) {
                    return $locale->getCode();
                }
            )->toArray();
            $availableLocales = implode(self::ITEM_SEPARATOR, $availableLocales);
        }

        $this->results['available_locales'] = $availableLocales ?: self::ALL_LOCALES;
    }

    /**
     * Normalize options
     *
     * @param ProductAttribute $attribute
     */
    protected function normalizeOptions($attribute)
    {
        $options = $attribute->getOptions();

        if ($options->isEmpty()) {
            $options = '';
        } else {
            $data = array();
            foreach ($options as $option) {
                $item = array();
                foreach ($option->getOptionValues() as $value) {
                    $label = str_replace('{locale}', $value->getLocale(), self::LOCALIZABLE_PATTERN);
                    $label = str_replace('{value}', $value->getValue(), $label);
                    $item[] = $label;
                }
                $data[] = implode(self::ITEM_SEPARATOR, $item);
            }
            $options = implode(self::GROUP_SEPARATOR, $data);
        }

        $this->results['options'] = $options;
    }

    /**
     * Normalize default options
     *
     * @param ProductAttribute $attribute
     */
    protected function normalizeDefaultOptions($attribute)
    {
        $defaultOptions = $attribute->getDefaultOptions();

        if ($defaultOptions->isEmpty()) {
            $defaultOptions = '';
        } else {
            $data = array();
            foreach ($defaultOptions as $option) {
                $item = array();
                foreach ($option->getOptionValues() as $value) {
                    $label = str_replace('{locale}', $value->getLocale(), self::LOCALIZABLE_PATTERN);
                    $label = str_replace('{value}', $value->getValue(), $label);
                    $item[] = $label;
                }
                $data[] = implode(self::ITEM_SEPARATOR, $item);
            }
            $defaultOptions = implode(self::GROUP_SEPARATOR, $data);
        }

        $this->results['default_options'] = $defaultOptions;
    }
}
