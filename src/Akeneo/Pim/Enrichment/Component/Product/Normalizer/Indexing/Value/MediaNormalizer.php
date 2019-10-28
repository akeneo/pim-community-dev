<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a media product value
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MediaValueInterface && (
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
        $data = $value->getData();

        if (null !== $data) {
            $normalizedMedia = [];

            $normalizedMedia['extension'] = $data->getExtension();
            $normalizedMedia['key'] = $data->getKey();
            $normalizedMedia['hash'] = $data->getHash();
            $normalizedMedia['mime_type'] = $data->getMimeType();
            $normalizedMedia['original_filename'] = $data->getOriginalFilename();
            $normalizedMedia['size'] = $data->getSize();
            $normalizedMedia['storage'] = $data->getStorage();

            return $normalizedMedia;
        }

        return null;
    }
}
