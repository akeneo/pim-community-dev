<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Abstract product value normalizer providing a product value path builder
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProductValueNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($productValue, $format = null, array $context = [])
    {
        $locale = (null === $productValue->getLocale()) ? '<all_locales>' : $productValue->getLocale();
        $channel = (null === $productValue->getScope()) ? '<all_channels>' : $productValue->getScope();

        $key = $productValue->getAttribute()->getCode() . '-' . $productValue->getAttribute()->getBackendType();
        $structure = [];
        $structure[$key][$channel][$locale] = $this->getNormalizedData($productValue);

        return $structure;
    }

    /**
     * Normalizes the product value data to the indexing format
     *
     * @param ProductValueInterface $productValue
     *
     * @return mixed
     **/
    abstract protected function getNormalizedData(ProductValueInterface $productValue);
}
