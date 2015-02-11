<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalizer for variant group values
 *
 * TODO : Should be re-worked to be used by ProductDenormalizer::denormalizeValues, to do so, it must be able to
 * create / update existing values related to a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesDenormalizer implements DenormalizerInterface
{
    /** @staticvar string */
    const PRODUCT_VALUES_TYPE = 'ProductValue[]';

    /** @var DenormalizerInterface */
    protected $valueDenormalizer;

    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /** @var string */
    protected $valueClass;

    /** @var string[] */
    protected $supportedFormats = array('csv');

    /**
     * @param DenormalizerInterface $valueDenormalizer
     * @param FieldNameBuilder      $fieldNameBuilder
     * @param string                $valueClass
     */
    public function __construct(
        DenormalizerInterface $valueDenormalizer,
        FieldNameBuilder $fieldNameBuilder,
        $valueClass
    ) {
        $this->valueDenormalizer = $valueDenormalizer;
        $this->fieldNameBuilder  = $fieldNameBuilder;
        $this->valueClass        = $valueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $productValues = [];
        foreach ($data as $attFieldName => $dataValue) {
            $attributeInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);

            if (null !== $attributeInfos) {
                $attribute = $attributeInfos['attribute'];
                unset($attributeInfos['attribute']);

                $valueKey = $attribute->getCode();
                if ($attribute->isLocalizable()) {
                    $valueKey .= '-' . $attributeInfos['locale_code'];
                }
                if ($attribute->isScopable()) {
                    $valueKey .= '-' . $attributeInfos['scope_code'];
                }
                if (isset($productValues[$valueKey])) {
                    $value = $productValues[$valueKey];
                } else {
                    $value = new $this->valueClass();
                    $value->setAttribute($attribute);
                    $value->setLocale($attributeInfos['locale_code']);
                    $value->setScope($attributeInfos['scope_code']);
                }

                $productValues[$valueKey] = $this->valueDenormalizer->denormalize(
                    $dataValue,
                    $this->valueClass,
                    'csv',
                    ['entity' => $value] + $attributeInfos
                );
            }
        }

        return new ArrayCollection($productValues);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === self::PRODUCT_VALUES_TYPE && in_array($format, $this->supportedFormats);
    }
}
