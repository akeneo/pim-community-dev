<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FieldProviderInterface */
    protected $fieldProvider;

    /** @var EmptyValueProviderInterface */
    protected $emptyValueProvider;

    /**
     * @param NormalizerInterface         $normalizer
     * @param FieldProviderInterface      $fieldProvider
     * @param EmptyValueProviderInterface $emptyValueProvider
     */
    public function __construct(
        NormalizerInterface $normalizer,
        FieldProviderInterface $fieldProvider,
        EmptyValueProviderInterface $emptyValueProvider
    ) {
        $this->normalizer         = $normalizer;
        $this->fieldProvider      = $fieldProvider;
        $this->emptyValueProvider = $emptyValueProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $dateMin = (null === $attribute->getDateMin()) ? '' : $attribute->getDateMin()->format(\DateTime::ISO8601);
        $dateMax = (null === $attribute->getDateMax()) ? '' : $attribute->getDateMax()->format(\DateTime::ISO8601);

        $normalizedAttribute = $this->normalizer->normalize($attribute, 'json', $context) + [
            'id'                    => $attribute->getId(),
            'wysiwyg_enabled'       => $attribute->isWysiwygEnabled(),
            'empty_value'           => $this->emptyValueProvider->getEmptyValue($attribute),
            'field_type'            => $this->fieldProvider->getField($attribute),
            'is_locale_specific'    => (int) $attribute->isLocaleSpecific(),
            'locale_specific_codes' => $attribute->getLocaleSpecificCodes(),
            'max_characters'        => $attribute->getMaxCharacters(),
            'validation_rule'       => $attribute->getValidationRule(),
            'validation_regexp'     => $attribute->getValidationRegexp(),
            'number_min'            => $attribute->getNumberMin(),
            'number_max'            => $attribute->getNumberMax(),
            'decimals_allowed'      => $attribute->isDecimalsAllowed(),
            'negative_allowed'      => $attribute->isNegativeAllowed(),
            'date_min'              => $dateMin,
            'date_max'              => $dateMax,
            'metric_family'         => $attribute->getMetricFamily(),
            'default_metric_unit'   => $attribute->getDefaultMetricUnit(),
            'max_file_size'         => $attribute->getMaxFileSize(),
        ];

        return $normalizedAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeInterface && in_array($format, $this->supportedFormats);
    }
}
