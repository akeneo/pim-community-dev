<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\ProductValue;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a text area product value
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextAreaNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueInterface &&
            AttributeTypes::BACKEND_TYPE_TEXTAREA === $data->getAttribute()->getBackendType() &&
            'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value)
    {
        $textAreaValue = $value->getData();

        if (null === $textAreaValue) {
            return null;
        }

        $cleanedData = str_replace(["\r", "\n"], "", $textAreaValue);
        $cleanedData = strip_tags(html_entity_decode($cleanedData));

        return $cleanedData;
    }
}
