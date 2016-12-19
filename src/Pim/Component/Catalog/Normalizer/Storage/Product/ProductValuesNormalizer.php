<?php

namespace Pim\Component\Catalog\Normalizer\Storage\Product;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalizer for a collection of product values
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = [])
    {
        $result = [];

        $esMappingSuffixes = [
             'pim_catalog_identifier' => '-ident-',
             'pim_catalog_text' => '-text-',
             'pim_catalog_textarea' => '-text_area-',
             'pim_catalog_metric' => '-metric-',
             'pim_catalog_boolean' => '-bool-',
             'pim_catalog_simpleselect' => '-option-',
             'pim_catalog_number' => '-number-',
             'pim_catalog_multiselect' => '-options-',
             'pim_catalog_date' => '-date-',
             'pim_catalog_price_collection' => '-prices-',
             'pim_catalog_image' => '-media-',
             'pim_catalog_file' => '-media-',
        ];                              
        
        foreach ($data as $value) {
            if (!$value instanceof ProductValueInterface) {
                throw new \InvalidArgumentException('This normalizer only handles "Pim\Component\Catalog\Model\ProductValueInterface".');
            }

            $stdValue = $this->serializer->normalize($value, 'standard', $context);

            $attribute = $value->getAttribute();
            $channel = null !== $stdValue['scope'] ? $stdValue['scope']->getCode() : '<all_channels>';
            $locale = null !== $stdValue['locale'] ? $stdValue['locale']->getCode() : '<all_locales>';

            $attributeCode = $attribute->getCode() . $esMappingSuffixes[$attribute->getAttributeType()];

            $result[$attributeCode][$channel][$locale] = $stdValue['data'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection && 'storage' === $format;
    }
}
