<?php

namespace Pim\Component\ReferenceData\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\AbstractProductValueNormalizer;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataProductValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataProductValue && 'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ProductValueInterface $productValue)
    {
        $data = $productValue->getData();
        if (null !== $data) {
            return $data->getCode();
        }

        return null;
    }
}
