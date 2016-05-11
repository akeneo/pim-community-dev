<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer as BaseAttributeNormalizer;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Pim\Bundle\EnrichBundle\Provider\Field\FieldProviderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute normalizer
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeNormalizer extends BaseAttributeNormalizer
{
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
        parent::__construct($normalizer, $fieldProvider, $emptyValueProvider);

        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $dateMin = (null === $attribute->getDateMin()) ? '' : $attribute->getDateMin()->format(\DateTime::ISO8601);
        $dateMax = (null === $attribute->getDateMax()) ? '' : $attribute->getDateMax()->format(\DateTime::ISO8601);
        $groupCode = (null === $attribute->getGroup()) ? null : $attribute->getGroup()->getCode();

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
                'sort_order'            => $attribute->getSortOrder(),
                'group_code'            => $groupCode,
                'is_read_only'          => $attribute->isReadOnly()
            ];

        // This normalizer is used in the PEF attributes loading and in the add_attributes widget. The attributes
        // loading does not need complete group normalization. This has to be cleaned.
        if (isset($context['include_group']) && $context['include_group'] && null !== $attribute->getGroup()) {
            $normalizedAttribute['group'] = $this->normalizer->normalize($attribute->getGroup(), 'json', $context);
        } else {
            $normalizedAttribute['group'] = null;
        }

        return $normalizedAttribute;
    }
}
