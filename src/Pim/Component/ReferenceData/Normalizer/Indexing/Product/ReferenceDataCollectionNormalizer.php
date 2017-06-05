<?php

namespace Pim\Component\ReferenceData\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\AbstractProductValueNormalizer;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataCollectionProductValue;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionNormalizer extends AbstractProductValueNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataCollectionProductValue && 'indexing' === $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ProductValueInterface $productValue)
    {
        return $productValue->getReferenceDataCodes();
    }
}
