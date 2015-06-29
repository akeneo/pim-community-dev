<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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

    /**
     * @param NormalizerInterface    $normalizer
     * @param FieldProviderInterface $fieldProvider
     */
    public function __construct(NormalizerInterface $normalizer, FieldProviderInterface $fieldProvider)
    {
        $this->normalizer    = $normalizer;
        $this->fieldProvider = $fieldProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attribute, $format = null, array $context = [])
    {
        $normalizedAttribute = $this->normalizer->normalize($attribute, 'json', $context) + [
            'id'              => $attribute->getId(),
            'wysiwyg_enabled' => $attribute->isWysiwygEnabled(),
            'empty_value'     => $this->getEmptyValue($attribute),
            'field_type'      => $this->fieldProvider->getField($attribute)
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

    /**
     * Get empty value for specified attribute depending on its type
     *
     * @param AttributeInterface $attribute
     *
     * @return array|bool|string|null
     */
    protected function getEmptyValue(AttributeInterface $attribute)
    {
        switch ($attribute->getAttributeType()) {
            case 'pim_catalog_metric':
                $emptyValue = [
                    'data' => null,
                    'unit' => $attribute->getDefaultMetricUnit(),
                ];
                break;
            case 'pim_catalog_multiselect':
            case 'pim_reference_data_multiselect':
                $emptyValue = [];
                break;
            case 'pim_catalog_text':
                $emptyValue = '';
                break;
            case 'pim_catalog_boolean':
                $emptyValue = false;
                break;
            case 'pim_catalog_price_collection':
                $emptyValue = [];
                break;
            default:
                $emptyValue = null;
                break;
        }

        return $emptyValue;
    }
}
