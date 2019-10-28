<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValueInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a date product value
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof DateValueInterface && (
                $format === ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
            );
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value)
    {
        $date = $value->getData();

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return null;
    }
}
