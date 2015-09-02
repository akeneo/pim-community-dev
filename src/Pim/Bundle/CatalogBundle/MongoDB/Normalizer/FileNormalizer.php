<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a file when normalizes a product value as mongodb_json
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = [])
    {
        return [
            'id'               => $file->getId(),
            'key'              => $file->getKey(),
            'originalFilename' => $file->getOriginalFilename()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && 'mongodb_json' === $format;
    }
}
