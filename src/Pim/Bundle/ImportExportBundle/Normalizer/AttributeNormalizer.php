<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * A normalizer to transform a ProductAttribute entity into array
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

    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var array
     */
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
            'label'                   => $this->normalizeLabel($attribute),
            'available_locales'       => $this->normalizeAvailableLocales($attribute),
            'group'                   => $attribute->getVirtualGroup()->getCode(),
            'sort_order'              => $attribute->getSortOrder(),
            'required'                => $attribute->getRequired(),
            'unique'                  => $attribute->getUnique(),
            'searchable'              => $attribute->getSearchable(),
            'localizable'             => $attribute->getTranslatable(),
            'scope'                   => $attribute->getScopable() ? self::CHANNEL_SCOPE : self::GLOBAL_SCOPE,
            'useable_as_grid_column'  => (string) (int) $attribute->isUseableAsGridColumn(),
            'useable_as_grid_filter'  => (string) (int) $attribute->isUseableAsGridFilter(),
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
            'allowed_extensions'      => implode(self::ITEM_SEPARATOR, $attribute->getAllowedExtensions()),
            'max_file_size'           => (string) $attribute->getMaxFileSize(),
            'options'                 => $this->normalizeOptions($attribute),
            'default_options'         => $this->normalizeDefaultOptions($attribute)
        );

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
     * @return array
     */
    protected function normalizeLabel(ProductAttribute $attribute)
    {
        $labels = array();
        foreach ($attribute->getTranslations() as $translation) {
            $labels[$translation->getLocale()]= $translation->getLabel();
        }

        return $labels;
    }

    /**
     * Normalize available locales
     *
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    protected function normalizeAvailableLocales($attribute)
    {
        $locales = array();
        foreach ($attribute->getAvailableLocales() as $locale) {
            $locales[]= $locale->getCode();
        }

        return $locales;
    }

    /**
     * Normalize options
     *
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    protected function normalizeOptions($attribute)
    {
        $data = array();
        $options = $attribute->getOptions();
        foreach ($options as $option) {
            $data[$option->getCode()]= array();
            foreach ($option->getOptionValues() as $value) {
                $data[$option->getCode()][$value->getLocale()]= $value->getValue();
            }
        }

        return $data;
    }

    /**
     * Normalize default options
     *
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    protected function normalizeDefaultOptions($attribute)
    {
        $data = array();
        $options = $attribute->getDefaultOptions();
        foreach ($options as $option) {
            $data[$option->getCode()]= array();
            foreach ($option->getOptionValues() as $value) {
                $data[$option->getCode()][$value->getLocale()]= $value->getValue();
            }
        }

        return $data;
    }
}
