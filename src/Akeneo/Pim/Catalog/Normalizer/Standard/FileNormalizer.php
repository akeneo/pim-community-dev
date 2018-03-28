<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
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
            'code'              => $file->getKey(),
            'original_filename' => $file->getOriginalFilename(),
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
            'extension'         => $file->getExtension()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && 'standard' === $format;
    }
}
