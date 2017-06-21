<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\ProductValue\MediaValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a media product value
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MediaValueInterface && 'indexing' === $format;
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
