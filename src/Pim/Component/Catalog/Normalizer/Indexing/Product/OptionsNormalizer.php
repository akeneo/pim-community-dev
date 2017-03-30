<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\ProductValue\OptionsProductValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a options product value
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OptionsNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface &&
            AttributeTypes::BACKEND_TYPE_OPTIONS === $data->getAttribute()->getBackendType() &&
            'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ProductValueInterface $productValue)
    {
        if ($productValue instanceof OptionsProductValue) {
            return $productValue->getOptionCodes();
        }

        throw InvalidObjectException::objectExpected(
            ClassUtils::getClass($productValue),
            OptionsProductValue::class
        );
    }
}
